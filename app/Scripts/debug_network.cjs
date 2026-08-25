const puppeteer = require('puppeteer-core');

(async () => {
    const executablePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const browser = await puppeteer.launch({
        executablePath, headless: 'new', ignoreHTTPSErrors: true,
        args: ['--no-sandbox','--disable-setuid-sandbox','--window-size=1280,800',
               '--disable-blink-features=AutomationControlled',
               '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36']
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    await page.goto('https://www.uvs-sesalhn.com/registro/login', { waitUntil: 'networkidle2' });
    await page.type('#txtNombreUsuario', 'vmeza');
    await page.type('#txtContrasenia', 'SM1885');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.evaluate(() => {
            const btn = document.getElementById('btnEntrar') || document.querySelector('input[type="submit"]');
            if (btn) btn.click(); else document.querySelector('form').submit();
        })
    ]);

    await page.goto('https://www.uvs-sesalhn.com/AlertaRespuesta/AtencionPasiva', { waitUntil: 'networkidle0' });
    await page.waitForSelector('#cphMain_frmlPaciente_txtNoIdentidad_I');

    // Asignar DNI
    await page.evaluate("window.cphMain_frmlPaciente_txtNoIdentidad.SetText('0801-2001-19707')");

    // Interceptar la respuesta del PostBack
    let postResponseBody = '';
    const [res] = await Promise.all([
        page.waitForResponse(res => res.url().includes('AtencionPasiva') && res.request().method() === 'POST'),
        page.evaluate("window.__doPostBack('ctl00$cphMain$frmlPaciente$txtNoIdentidad', '')")
    ]);
    postResponseBody = await res.text();

    console.log('LONGITUD RESPUESTA POST:', postResponseBody.length);

    // Buscar nombres en la respuesta POST
    const matchNombres = postResponseBody.match(/txtNombres_I"[^>]*value="([^"]*)"/);
    console.log('MATCH NOMBRES EN RESPONSE:', matchNombres ? matchNombres[1] : 'NO MATCH');

    const matchApellidos = postResponseBody.match(/txtApellidos_I"[^>]*value="([^"]*)"/);
    console.log('MATCH APELLIDOS EN RESPONSE:', matchApellidos ? matchApellidos[1] : 'NO MATCH');

    // Esperar 2 segundos y leer el DOM
    await new Promise(r => setTimeout(r, 2000));

    const domVal = await page.evaluate(() => {
        const v = id => { const el = document.getElementById(id); return el ? el.value : 'NULL'; };
        const devV = id => { const ctrl = window[id]; return ctrl && ctrl.GetText ? ctrl.GetText() : 'NULL'; };
        return {
            dom_nombres: v('cphMain_frmlPaciente_txtNombres_I'),
            dev_nombres: devV('cphMain_frmlPaciente_txtNombres'),
            dom_apellidos: v('cphMain_frmlPaciente_txtApellidos_I'),
            dev_apellidos: devV('cphMain_frmlPaciente_txtApellidos'),
            dom_fechaNac: v('cphMain_frmlPaciente_dtFechaNacimiento_I'),
            dom_genero: v('cphMain_frmlPaciente_cmbGenero_I')
        };
    });

    console.log('DOM VALUES:\n', JSON.stringify(domVal, null, 2));

    await browser.close();
})();
