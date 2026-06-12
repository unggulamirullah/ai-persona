# MASTER PROJECT CONTEXT: AI Persona Encyclopedia (V2)
Tolong baca dan pahami dokumen ini sebagai aturan dasar proyek. Jangan generate kode apa pun dulu. Cukup balas dengan "CONTEXT ACCEPTED" jika kamu sudah paham.

## 1. Tech Stack & Environment
- Backend: Laravel (PHP)
- Database: MySQL
- Frontend: Blade Templates, TailwindCSS (via CDN), Vanilla JavaScript.
- Arsitektur: Monolith dengan AJAX API Calls (tanpa reload halaman untuk pencarian dan chat).

## 2. Database Schema (MySQL)
Tabel: `characters`
- `id` (Primary Key)
- `api_id` (Unsigned Big Integer, Unique) -> Menyimpan ID dari Jikan API.
- `name` (String) -> Nama karakter.
- `image_url` (String, Nullable) -> URL gambar (fetched from web/Jikan API).
- `lore` (Text, Nullable) -> Latar belakang/deskripsi karakter dari Jikan API.
- `timestamps`

## 3. External API Endpoints
- Search API (Jikan V4): `GET https://api.jikan.moe/v4/characters?q={query}&limit=5`
- Detail API (Jikan V4): `GET https://api.jikan.moe/v4/characters/{id}`
- LLM API (Groq/Gemini): Endpoint standar menggunakan Http Facade Laravel untuk mengirim prompt ke model AI bahasa.

## 4. Core Features Logic (Aturan Wajib Backend & Frontend)
- Live Search Autocomplete: Pada halaman utama, wajib menggunakan event listener `input` (JavaScript) dengan fitur Debouncing (300ms) untuk mencegah spam API. Tampilkan hasil rekomendasi karakter dalam bentuk absolute div (dropdown) di bawah search bar.
- Lazy Fetching (Database Cache): Saat detail karakter diklik, Controller WAJIB mengecek database lokal `characters` terlebih dahulu berdasarkan `api_id`. 
  - Jika belum ada: Lakukan HTTP Request ke Detail API (Jikan), simpan `name`, `image_url`, dan `lore` ke database MySQL, lalu tampilkan ke view.
  - Jika sudah ada: Langsung ambil data dari database lokal (hemat API call).
- AI Persona Chatbot: Fitur interaktif di halaman detail menggunakan AJAX (Fetch API). Di backend, racik prompt menggunakan format berikut sebelum menembak LLM API: "System: Kamu adalah {character_name}. Ini latar belakangmu: {lore}. Jawab pertanyaan user berikut murni dengan gaya bahasa, sifat, pandangan hidup, dan emosimu. Jangan keluar dari karakter."

## 5. UI/UX Guidelines (Ultra-Modern & Elegant)
- Framework: Wajib gunakan TailwindCSS.
- Tema Utama: Deep Dark Aesthetic (misalnya bg-slate-900). Jangan hitam pekat polos.
- Tipografi: Import dan gunakan font modern seperti 'Inter' atau 'Poppins' dari Google Fonts.
- Gaya Visual (Wajib): 
  1. Terapkan "Glassmorphism" pada panel informasi lore dan panel AI chatbox (gunakan kombinasi `bg-white/10`, `backdrop-blur-md`, `border border-white/20`, dan `shadow-xl`).
  2. Gunakan efek "Glow" atau bayangan berpendar (glow shadow) tipis di belakang elemen penting (gambar atau container).
- Aksen Warna: Gunakan warna gradient kekinian untuk tombol, border, atau aksen teks judul (misalnya `bg-gradient-to-r from-purple-500 to-cyan-500`).
- Animasi & Interaksi: Setiap gambar atau kartu interaktif HARUS memiliki efek transisi halus (`transition-all duration-300 hover:scale-105 hover:shadow-2xl`).
- Layout Halaman Detail (View): Gunakan CSS Grid atau Flexbox. Gambar karakter dan detail lore berada di sisi kiri (lebar 60%), sedangkan AI Chatbox di sisi kanan (lebar 40%) agar terlihat seperti dashboard sci-fi yang elegan.

---
ATURAN EKSEKUSI:
Jika kamu sudah memahami seluruh konteks di atas, balas HANYA dengan "CONTEXT ACCEPTED". Saya akan memberikan prompt langkah demi langkah setelahnya.