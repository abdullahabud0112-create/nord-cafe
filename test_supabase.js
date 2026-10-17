import fs from 'fs';
import path from 'path';

// Read .env
const envPath = path.resolve('.env');
let supabaseUrl = '';
let supabaseKey = '';

if (fs.existsSync(envPath)) {
    const lines = fs.readFileSync(envPath, 'utf8').split('\n');
    for (const line of lines) {
        const trimmed = line.trim();
        if (trimmed.startsWith('NEXT_PUBLIC_SUPABASE_URL=')) {
            supabaseUrl = trimmed.split('=')[1].trim().replace(/['"]/g, '');
        }
        if (trimmed.startsWith('NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY=')) {
            supabaseKey = trimmed.split('=')[1].trim().replace(/['"]/g, '');
        }
    }
}

console.log('--- SUPABASE CONFIG CHECK ---');
console.log('URL:', supabaseUrl || '(not found)');
console.log('Key:', supabaseKey ? supabaseKey.substring(0, 16) + '...' : '(not found)');

if (!supabaseUrl) {
    console.error('Error: NEXT_PUBLIC_SUPABASE_URL not found in .env');
    process.exit(1);
}

try {
    const urlObj = new URL(supabaseUrl);
    console.log('Testing connection to:', urlObj.origin);

    // Test a table endpoint (categories) using the publishable/anon key
    const testEndpoints = ['categories', 'products', 'customers'];
    let connected = false;

    for (const table of testEndpoints) {
        const res = await fetch(`${urlObj.origin}/rest/v1/${table}?select=*&limit=3`, {
            headers: {
                'apikey': supabaseKey,
                'Authorization': `Bearer ${supabaseKey}`
            }
        });
        
        console.log(`Endpoint /rest/v1/${table} -> Status: ${res.status} ${res.statusText}`);
        if (res.ok) {
            const data = await res.json();
            console.log(`\n🎉 SUCCESS: Berhasil terhubung ke Supabase!`);
            console.log(`Data dari tabel '${table}':`, data);
            connected = true;
            break;
        } else {
            const errText = await res.text();
            console.log(`Response tabel '${table}':`, errText);
        }
    }

    if (!connected) {
        console.log('\nCatatan: Jika muncul "relation ... does not exist", artinya tabel belum dibuat di Supabase.');
        console.log('Jalankan isi file supabase_schema_and_seed.sql di SQL Editor Supabase terlebih dahulu.');
    }
} catch (err) {
    console.log('CONNECTION ERROR:', err.message);
}
