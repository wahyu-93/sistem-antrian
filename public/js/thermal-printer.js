let port = null;
async function getPrinter()
{
    try {
        port = await navigator.serial.requestPort();
        await port.open({ baudRate: 9600 });
        alert("Printer terhubung!");
    } catch (err) {
        alert("Gagal konek: " + err.message);
    }
}

async function printThermal(text) {
    if (!port) {
        alert("Hubungkan dulu printer.");
        return;
    }

    // const nota = '\x1B@\x1Ba\x01Sistem Antrian Sample\n' +
    //          '\x1Ba\x01Jl. 12345\n' +
    //          '\x1Ba\x01---------------------\n' +
    //          '\x1Ba\x01NOMOR ANTRIAN\n' +
    //          '\x1Ba\x01A001\n' +
    //          '\x1Ba\x01---------------------\n' +
    //          '\x1Ba\x01Mohon menunggu\n' +
    //          '\x1Ba\x01Terima kasih\n\n\n\n' +
    //          '\x1D\x56\x41\x00';

    try {
        // Cek apakah stream tidak sedang terkunci
        if (port.writable.locked) {
            alert("Printer sedang sibuk. Silakan tunggu sebentar.");
            return;
        }

        const writer = port.writable.getWriter();
        const encoder = new TextEncoder();

        await writer.write(encoder.encode(text));
        writer.releaseLock();

        // alert("Nota dicetak!");
        console.log(text);
    } catch (err) {
        alert("Gagal cetak: " + err.message);
    }
}

// function asciiEncode(str) {
//     const bytes = [];
//     for (let i = 0; i < str.length; i++) {
//         const code = str.charCodeAt(i);
//         bytes.push(code < 128 ? code : 63); // replace non-ASCII with '?'
//     }
//     return new Uint8Array(bytes);
// }

// async function printThermal(text) {
//     if (!port) {
//         alert("Hubungkan dulu printer.");
//         return;
//     }

//     try {
//         if (port.writable.locked) {
//             alert("Printer sedang sibuk. Silakan tunggu.");
//             return;
//         }

//         const writer = port.writable.getWriter();

//       const esc = '\x1B';
//       const gs = '\x1D';
//       const nota = 
//         esc + '@' +
//         'TOKO ABC\n' +
//         '--------------------------\n' +
//         'Produk A      Rp10.000\n' +
//         'Produk B      Rp5.000\n' +
//         '--------------------------\n' +
//         'Total         Rp15.000\n' +
//         '\nTerima Kasih\n\n\n' +
//         gs + 'V' + '\x00'; // Cut

//         await writer.write(asciiEncode(nota));
//         writer.releaseLock();

//         alert("Nota dicetak!");
//     } catch (err) {
//         alert("Gagal cetak: " + err.message);
//     }
// }