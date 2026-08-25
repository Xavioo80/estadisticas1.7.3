const puppeteer = require('puppeteer-core');
const fs = require('fs');

(async () => {
    const executablePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    if (!fs.existsSync(executablePath)) {
        console.log(JSON.stringify({ error: 'Chrome not found at ' + executablePath }));
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        executablePath,
        headless: 'new',
        ignoreHTTPSErrors: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1280,800',
               '--disable-blink-features=AutomationControlled',
               '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });
    await page.evaluateOnNewDocument(() => {
        Object.defineProperty(navigator, 'webdriver', { get: () => false });
    });

    await page.goto('https://www.uvs-sesalhn.com/registro/login', { waitUntil: 'networkidle2', timeout: 30000 });
    console.log('1. PAGINA: ' + page.url());
    
    await page.waitForSelector('#txtNombreUsuario', { timeout: 10000 });
    await page.type('#txtNombreUsuario', 'vmeza', { delay: 50 });
    await page.type('#txtContrasenia', 'SM1885', { delay: 50 });

    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 20000 }).catch(() => {}),
        page.click('#btnEntrar_I').catch(() => page.click('#btnEntrar'))
    ]);

    console.log('2. DESPUES LOGIN: ' + page.url());
    const htmlAfter = await page.content();
    console.log('3. CONTIENE vmeza/VMEZA: ' + (htmlAfter.includes('vmeza') || htmlAfter.includes('VMEZA')));
    console.log('4. CONTIENE panel/dashboard: ' + (htmlAfter.includes('panel') || htmlAfter.includes('dashboard')));

    // Intentar navegar a AtencionPasiva
    await page.goto('https://www.uvs-sesalhn.com/AlertaRespuesta/AtencionPasiva', { waitUntil: 'networkidle2', timeout: 20000 });
    console.log('5. ATENCION PASIVA URL: ' + page.url());
    
    const htmlAtencion = await page.content();
    console.log('6. TIENE FORMULARIO: ' + htmlAtencion.includes('frmlPaciente'));
    console.log('7. TIENE LOGIN: ' + htmlAtencion.includes('txtNombreUsuario'));
    
    // Obtener title para saber en qué página estamos
    const title = await page.title();
    console.log('8. TITLE: ' + title);

    await browser.close();
})();
