const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

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

(async () => {
    const startTime = Date.now();
    const args = process.argv.slice(2);
    let identidad = '';
    for (const arg of args) {
        if (arg.startsWith('--identidad=')) identidad = arg.split('=')[1];
    }

    if (!identidad) {
        console.log(JSON.stringify({ success: false, message: "Identidad requerida" }));
        process.exit(1);
    }

    const cleanDni = identidad.replace(/\D/g, '');
    const formattedDni = cleanDni.length === 13
        ? `${cleanDni.slice(0,4)}-${cleanDni.slice(4,8)}-${cleanDni.slice(8,13)}`
        : identidad;

    const cookieFilePath = path.join(__dirname, '..', '..', 'storage', 'app', 'sesal_cookies.json');

    try {
        const executablePath = await getExecutablePath();
        const browser = await puppeteer.launch({
            executablePath,
            headless: 'new',
            ignoreHTTPSErrors: true,
            args: [
                '--no-sandbox', '--disable-setuid-sandbox',
                '--disable-blink-features=AutomationControlled',
                '--disable-gpu', '--disable-dev-shm-usage',
                '--no-first-run', '--no-zygote',
                '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        });

        const page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 800 });

        await page.setRequestInterception(true);
        page.on('request', (req) => {
            if (['image', 'font', 'media'].includes(req.resourceType())) {
                req.abort();
            } else {
                req.continue();
            }
        });

        let isLoggedIn = false;

        if (fs.existsSync(cookieFilePath)) {
            try {
                const savedCookies = JSON.parse(fs.readFileSync(cookieFilePath, 'utf8'));
                if (Array.isArray(savedCookies) && savedCookies.length > 0) {
                    await page.setCookie(...savedCookies);
                    await page.goto('https://www.uvs-sesalhn.com/AlertaRespuesta/AtencionPasiva', { waitUntil: 'domcontentloaded', timeout: 15000 });
                    if (!page.url().includes('login') && await page.$('#cphMain_frmlPaciente_txtNoIdentidad_I') !== null) {
                        isLoggedIn = true;
                    }
                }
            } catch (e) { isLoggedIn = false; }
        }

        if (!isLoggedIn) {
            await page.goto('https://www.uvs-sesalhn.com/registro/login', { waitUntil: 'domcontentloaded', timeout: 20000 });
            await page.waitForSelector('#txtNombreUsuario', { timeout: 10000 });
            await page.type('#txtNombreUsuario', 'vmeza', { delay: 10 });
            await page.type('#txtContrasenia', 'SM1885', { delay: 10 });

            await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
                page.evaluate(() => {
                    const btn = document.getElementById('btnEntrar') || document.querySelector('input[type="submit"]');
                    if (btn) btn.click(); else document.querySelector('form').submit();
                })
            ]);

            await page.goto('https://www.uvs-sesalhn.com/AlertaRespuesta/AtencionPasiva', { waitUntil: 'domcontentloaded', timeout: 20000 });
            await page.waitForSelector('#cphMain_frmlPaciente_txtNoIdentidad_I', { timeout: 10000 });

            const cookies = await page.cookies('https://www.uvs-sesalhn.com');
            try {
                const dir = path.dirname(cookieFilePath);
                if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
                fs.writeFileSync(cookieFilePath, JSON.stringify(cookies, null, 2));
            } catch (e) {}
        }

        // Limpiar campos previos
        await page.evaluate(() => {
            const clearVal = id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
                const ctrlName = id.replace('_I', '');
                if (window[ctrlName] && window[ctrlName].SetText) {
                    try { window[ctrlName].SetText(''); } catch(e){}
                }
            };
            clearVal('cphMain_frmlPaciente_txtNombres_I');
            clearVal('cphMain_frmlPaciente_txtApellidos_I');
            clearVal('cphMain_frmlPaciente_dtFechaNacimiento_I');
            clearVal('cphMain_frmlPaciente_cmbGenero_I');
            clearVal('cphMain_frmlPaciente_txtTelefono_I');
            clearVal('cphMain_frmlPaciente_txtDireccion_I');
        });

        // Asignar Identidad
        await page.evaluate(`window.cphMain_frmlPaciente_txtNoIdentidad.SetText('${formattedDni}')`);

        // Disparar PostBack oficial
        await Promise.all([
            page.waitForResponse(res => res.url().includes('AtencionPasiva') && res.request().method() === 'POST', { timeout: 10000 }).catch(() => {}),
            page.evaluate("window.__doPostBack('ctl00$cphMain$frmlPaciente$txtNoIdentidad', '')")
        ]);

        await page.waitForFunction(
            () => {
                const el = document.getElementById('cphMain_frmlPaciente_txtNombres_I');
                return el && el.value && el.value.trim().length > 0;
            },
            { timeout: 4000 }
        ).catch(() => {});

        // En caso de que salga un cuadro de información (ej. Historial ENO), quitarlo dando click afuera o con Escape
        try {
            await page.keyboard.press('Escape');
            await page.mouse.click(20, 20);
            await page.evaluate(() => {
                if (window.ASPxClientControl && ASPxClientControl.GetControlCollection) {
                    try {
                        ASPxClientControl.GetControlCollection().ForEachControl(c => {
                            if (c && typeof c.Hide === 'function' && typeof c.IsVisible === 'function' && c.IsVisible()) {
                                c.Hide();
                            }
                        });
                    } catch(e) {}
                }
                const bg = document.querySelector('.dxpc-modalBackground, .modal-backdrop, .dxpcDropDownModalBackground, [class*="modalBackground"]');
                if (bg) bg.click();
            });
        } catch(e) {}

        const data = await page.evaluate(() => {
            const v = id => { const e = document.getElementById(id); return e ? (e.value || '').trim() : ''; };
            const txt = id => { const e = document.getElementById(id); return e ? (e.textContent || '').trim() : ''; };

            let rawEdad = txt('cphMain_frmlPaciente_lblEdad');
            let edadNum = '';
            let edadTipo = 'A';
            if (rawEdad) {
                const match = rawEdad.match(/(\d+)\s*(AÑO|AÑOS|MES|MESES|DIA|DIAS|DÍAS)?/i);
                if (match) {
                    edadNum = match[1];
                    const unit = (match[2] || '').toUpperCase();
                    if (unit.startsWith('M')) edadTipo = 'M';
                    else if (unit.startsWith('D')) edadTipo = 'D';
                    else edadTipo = 'A';
                }
            }

            return {
                identidad:        v('cphMain_frmlPaciente_txtNoIdentidad_I'),
                nombres:          v('cphMain_frmlPaciente_txtNombres_I'),
                apellidos:        v('cphMain_frmlPaciente_txtApellidos_I'),
                fecha_nacimiento: v('cphMain_frmlPaciente_dtFechaNacimiento_I'),
                genero:           v('cphMain_frmlPaciente_cmbGenero_I'),
                telefono:         v('cphMain_frmlPaciente_txtTelefono_I'),
                edad:             edadNum,
                tipo:             edadTipo,
                departamento:     v('cphMain_frmlPaciente_cmbDepartamento_I'),
                municipio:        v('cphMain_frmlPaciente_cmbMunicipio_I'),
                direccion:        v('cphMain_frmlPaciente_txtDireccion_I'),
                colonia:          v('cphMain_frmlPaciente_txtDireccion_I')
            };
        });

        await browser.close();

        data.nombre_completo = `${data.nombres} ${data.apellidos}`.trim();
        data.success = (data.nombres.length > 0 || data.apellidos.length > 0);
        if (!data.identidad) data.identidad = formattedDni;
        data.elapsed_ms = Date.now() - startTime;

        console.log(JSON.stringify(data));

    } catch (err) {
        console.log(JSON.stringify({ success: false, message: "Error: " + err.message }));
    }
})();
