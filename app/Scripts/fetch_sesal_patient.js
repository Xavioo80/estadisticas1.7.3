const puppeteer = require('puppeteer-core');
const fs = require('fs');

async function getExecutablePath() {
    const paths = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Users\\CISSM\\AppData\\Local\\Google\\Chrome\\Application\\chrome.exe'
    ];
    for (const p of paths) {
        if (fs.existsSync(p)) return p;
    }
    throw new Error("No se encontró ejecutable de Chrome o Edge en el sistema");
}

(async () => {
    const args = process.argv.slice(2);
    let identidad = '';
    for (const arg of args) {
        if (arg.startsWith('--identidad=')) {
            identidad = arg.split('=')[1];
        }
    }

    if (!identidad) {
        console.log(JSON.stringify({ success: false, message: "Identidad requerida" }));
        process.exit(1);
    }

    const cleanDni = identidad.replace(/\D/g, '');
    let formattedDni = identidad;
    if (cleanDni.length === 13) {
        formattedDni = `${cleanDni.slice(0,4)}-${cleanDni.slice(4,8)}-${cleanDni.slice(8,13)}`;
    }

    try {
        const executablePath = await getExecutablePath();
        const browser = await puppeteer.launch({
            executablePath,
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-web-security']
        });

        const page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 800 });

        // 1. Iniciar Sesión en uvs-sesalhn.com
        await page.goto('https://www.uvs-sesalhn.com/registro/login', { waitUntil: 'networkidle2' });
        await page.type('#txtNombreUsuario', 'vmeza');
        await page.type('#txtContrasenia', 'SM1885');
        await page.click('#btnEntrar');

        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});

        // 2. Ir a Atención Pasiva
        await page.goto('https://www.uvs-sesalhn.com/AlertaRespuesta/AtencionPasiva', { waitUntil: 'networkidle2' });

        // 3. Escribir número de Identidad y presionar Tab
        const inputSelector = '#cphMain_frmlPaciente_txtNoIdentidad_I';
        await page.waitForSelector(inputSelector, { timeout: 10000 });
        await page.click(inputSelector);
        
        await page.keyboard.down('Control');
        await page.keyboard.press('A');
        await page.keyboard.up('Control');
        await page.keyboard.press('Backspace');
        
        await page.type(inputSelector, formattedDni, { delay: 40 });
        await page.keyboard.press('Tab');

        // Esperar 2.5 segundos para la resolución del callback de DevExpress
        await new Promise(r => setTimeout(r, 2500));

        // 4. Extraer los datos del formulario
        const data = await page.evaluate(() => {
            const getVal = (id) => {
                const el = document.getElementById(id);
                return el ? (el.value || el.innerText || '').trim() : '';
            };

            return {
                nombres: getVal('cphMain_frmlPaciente_txtNombres_I'),
                apellidos: getVal('cphMain_frmlPaciente_txtApellidos_I'),
                fecha_nacimiento: getVal('cphMain_frmlPaciente_dtFechaNacimiento_I'),
                genero: getVal('cphMain_frmlPaciente_cmbGenero_I'),
                telefono: getVal('cphMain_frmlPaciente_txtTelefono_I'),
                direccion: getVal('cphMain_frmlPaciente_txtDireccion_I') || getVal('cphMain_frmlPaciente_cmbDireccion_I')
            };
        });

        await browser.close();

        data.nombre_completo = `${data.nombres} ${data.apellidos}`.trim();
        data.success = true;
        data.identidad = formattedDni;

        console.log(JSON.stringify(data));

    } catch (err) {
        console.log(JSON.stringify({
            success: false,
            message: "Error al consultar en SESAL: " + err.message
        }));
    }
})();
