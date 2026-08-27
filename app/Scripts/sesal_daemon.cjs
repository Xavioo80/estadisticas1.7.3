const http = require('http');
const puppeteer = require('puppeteer-core');
const fs = require('fs');

const PORT = 9099;
let pageInstance = null;
let browserInstance = null;
let isReady = false;
let isInitializing = false;

async function getExecutablePath() {
    const paths = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Users\\CISSM\\AppData\\Local\\Google\\Chrome\\Application\\chrome.exe',
    ];
    for (const p of paths) { if (fs.existsSync(p)) return p; }
    throw new Error("No se encontró Chrome o Edge");
}

async function initBrowserSession() {
    if (isInitializing) return;
    isInitializing = true;
    isReady = false;
    try {
        console.log('[DAEMON] Iniciando navegador Chrome...');
        const executablePath = await getExecutablePath();
        
        if (browserInstance) {
            try { await browserInstance.close(); } catch(e) {}
        }

        browserInstance = await puppeteer.launch({
            executablePath,
            headless: 'new',
            ignoreHTTPSErrors: true,
            args: [
                '--no-sandbox', '--disable-setuid-sandbox',
                '--disable-blink-features=AutomationControlled',
                '--disable-gpu', '--disable-dev-shm-usage',
                '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        });

        pageInstance = await browserInstance.newPage();
        await pageInstance.setViewport({ width: 1280, height: 800 });

        await pageInstance.setRequestInterception(true);
        pageInstance.on('request', (req) => {
            if (['image', 'font', 'media'].includes(req.resourceType())) {
                req.abort();
            } else {
                req.continue();
            }
        });

        console.log('[DAEMON] Iniciando sesión en SESAL...');
        await pageInstance.goto('https://www.uvs-sesalhn.com/registro/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
        await pageInstance.waitForSelector('#txtNombreUsuario', { timeout: 10000 });
        await pageInstance.type('#txtNombreUsuario', 'vmeza', { delay: 10 });
        await pageInstance.type('#txtContrasenia', 'SM1885', { delay: 10 });

        await Promise.all([
            pageInstance.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 20000 }).catch(() => {}),
            pageInstance.evaluate(() => {
                const btn = document.getElementById('btnEntrar') || document.querySelector('input[type="submit"]');
                if (btn) btn.click(); else document.querySelector('form').submit();
            })
        ]);

        console.log('[DAEMON] Cargando formulario de Atención Pasiva...');
        await pageInstance.goto('https://www.uvs-sesalhn.com/AlertaRespuesta/AtencionPasiva', { waitUntil: 'domcontentloaded', timeout: 20000 });
        await pageInstance.waitForSelector('#cphMain_frmlPaciente_txtNoIdentidad_I', { timeout: 15000 });

        isReady = true;
        console.log('[DAEMON] Sesión lista para recibir consultas.');
    } catch (err) {
        console.error('[DAEMON ERROR INICIALIZACION]:', err.message);
        isReady = false;
    } finally {
        isInitializing = false;
    }
}

async function lookupPatient(identidad) {
    const cleanDni = identidad.replace(/\D/g, '');
    const formattedDni = cleanDni.length === 13
        ? `${cleanDni.slice(0,4)}-${cleanDni.slice(4,8)}-${cleanDni.slice(8,13)}`
        : identidad;

    let waitAttempts = 0;
    while (!isReady && waitAttempts < 15) {
        if (!isInitializing) {
            await initBrowserSession();
        }
        await new Promise(r => setTimeout(r, 1000));
        waitAttempts++;
    }

    if (!isReady || !pageInstance || pageInstance.isClosed()) {
        throw new Error("El demonio no pudo inicializar la sesión en SESAL.");
    }

    if (pageInstance.url().includes('login')) {
        console.log('[DAEMON] Sesión expirada. Re-autenticando...');
        await initBrowserSession();
    }

    const startTime = Date.now();

    await pageInstance.waitForSelector('#cphMain_frmlPaciente_txtNoIdentidad_I', { timeout: 5000 });

    // 1. Asignar nueva Identidad
    await pageInstance.evaluate((dni) => {
        if (window.cphMain_frmlPaciente_txtNoIdentidad && window.cphMain_frmlPaciente_txtNoIdentidad.SetText) {
            window.cphMain_frmlPaciente_txtNoIdentidad.SetText(dni);
        } else {
            const el = document.getElementById('cphMain_frmlPaciente_txtNoIdentidad_I');
            if (el) el.value = dni;
        }
    }, formattedDni);

    // 2. Disparar PostBack oficial de ASP.NET y capturar respuesta directamente de red
    let postResponseBody = '';
    const [res] = await Promise.all([
        pageInstance.waitForResponse(res => res.url().includes('AtencionPasiva') && res.request().method() === 'POST', { timeout: 10000 }).catch(() => null),
        pageInstance.evaluate("window.__doPostBack('ctl00$cphMain$frmlPaciente$txtNoIdentidad', '')")
    ]);

    if (res) {
        try { postResponseBody = await res.text(); } catch(e) {}
    }

    // 3. Extraer valores del HTML de la respuesta POST (100% confiable)
    const extractFromHtml = (html, id) => {
        const reg1 = new RegExp(`${id}"[^>]*value="([^"]*)"`, 'i');
        const m1 = html.match(reg1);
        if (m1 && m1[1]) return m1[1].trim();

        const reg2 = new RegExp(`value="([^"]*)"[^>]*id="${id}"`, 'i');
        const m2 = html.match(reg2);
        if (m2 && m2[1]) return m2[1].trim();

        return '';
    };

    let nombres         = extractFromHtml(postResponseBody, 'txtNombres_I');
    let apellidos       = extractFromHtml(postResponseBody, 'txtApellidos_I');
    let fechaNacimiento = extractFromHtml(postResponseBody, 'dtFechaNacimiento_I');
    let genero          = extractFromHtml(postResponseBody, 'cmbGenero_I');
    let telefono        = extractFromHtml(postResponseBody, 'txtTelefono_I');

    // 4. Si la extracción de red trajo valores, dar tiempo a que se reflejen en el DOM también
    await new Promise(r => setTimeout(r, 600));

    // Fallback: Si algún valor no se capturó por regex de red, leerlo del DOM
    const domData = await pageInstance.evaluate(() => {
        const v = id => { const e = document.getElementById(id); return e ? (e.value || '').trim() : ''; };
        return {
            nombres:          v('cphMain_frmlPaciente_txtNombres_I'),
            apellidos:        v('cphMain_frmlPaciente_txtApellidos_I'),
            fecha_nacimiento: v('cphMain_frmlPaciente_dtFechaNacimiento_I'),
            genero:           v('cphMain_frmlPaciente_cmbGenero_I'),
            telefono:         v('cphMain_frmlPaciente_txtTelefono_I'),
        };
    });

    if (!nombres) nombres = domData.nombres;
    if (!apellidos) apellidos = domData.apellidos;
    if (!fechaNacimiento) fechaNacimiento = domData.fecha_nacimiento;
    if (!genero) genero = domData.genero;
    if (!telefono) telefono = domData.telefono;

    const data = {
        identidad: formattedDni,
        nombres,
        apellidos,
        nombre_completo: `${nombres} ${apellidos}`.trim(),
        fecha_nacimiento: fechaNacimiento,
        genero,
        telefono,
        success: (nombres.length > 0 || apellidos.length > 0),
        elapsed_ms: Date.now() - startTime
    };

    return data;
}

const server = http.createServer(async (req, res) => {
    res.setHeader('Content-Type', 'application/json; charset=utf-8');
    res.setHeader('Access-Control-Allow-Origin', '*');

    const urlObj = new URL(req.url, `http://${req.headers.host}`);

    if (urlObj.pathname === '/buscar') {
        const identidad = urlObj.searchParams.get('identidad') || '';
        if (!identidad) {
            res.statusCode = 400;
            return res.end(JSON.stringify({ success: false, message: 'Identidad requerida' }));
        }

        try {
            const result = await lookupPatient(identidad);
            res.statusCode = 200;
            return res.end(JSON.stringify(result));
        } catch (err) {
            res.statusCode = 500;
            return res.end(JSON.stringify({ success: false, message: err.message }));
        }
    } else if (urlObj.pathname === '/ping') {
        res.statusCode = 200;
        return res.end(JSON.stringify({ status: 'ok', ready: isReady }));
    }

    res.statusCode = 404;
    res.end(JSON.stringify({ error: 'Ruta no encontrada' }));
});

server.listen(PORT, '127.0.0.1', () => {
    console.log(`[DAEMON] Servidor de consultas SESAL escuchando en http://127.0.0.1:${PORT}`);
    initBrowserSession();
});
