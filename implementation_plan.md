# Implementation Plan: Dynamic Emotion UI & Multi-Character Clash

Proyek ini akan dikembangkan dengan dua fitur utama baru: **Reaksi Emosi Dinamis** pada karakter dan **Arena Grup Chat (Clash)** antar 2 karakter.

## User Review Required
> [!IMPORTANT]
> Mohon baca rancangan sistem di bawah ini. Fitur Clash (Grup Chat) membutuhkan pendekatan khusus pada UI dan arsitektur *Prompting* LLM agar performanya tetap cepat. Apakah pendekatan "Satu kali panggilan API untuk dua karakter" sudah sesuai dengan keinginan Anda?

## Open Questions
> [!WARNING]  
> 1. Untuk **Arena Grup Chat**, apakah Anda setuju kita membuat halaman khusus (`/clash`) di mana Anda bisa memilih 2 karakter sebelum masuk ke arena obrolan?
> 2. Untuk **Emosi**, saya merencanakan 4 emosi dasar (*neutral*, *happy*, *angry*, *sad*). Apakah Anda ingin menambahkan emosi lain (misal: *shocked*, *smug*)?

## Proposed Changes

---

### 1. Dynamic Emotion UI (Fitur 3)

Fitur ini akan diimplementasikan pada halaman detail karakter (`show.blade.php`).

#### [MODIFY] `app/Http/Controllers/CharacterController.php`
- **Ubah System Prompt:** Menginstruksikan LLM (Groq) agar mereturn respons wajib dalam format JSON ketat: `{"emotion": "angry|happy|sad|neutral", "reply": "pesan teks"}`.
- **Parsing Response:** Backend akan mengembalikan format JSON yang rapi ke *frontend*.

#### [MODIFY] `resources/views/character/show.blade.php`
- **Tweak JS AJAX:** Melakukan *parsing* JSON dari backend untuk mengambil `emotion` dan `reply`.
- **CSS Classes & Animasi:**
  - `emotion-angry`: UI berubah warna menjadi *glow merah* (`shadow-red-500`) dan *bubble chat* bergetar pelan (*shake animation*).
  - `emotion-happy`: UI berubah warna menjadi *glow kuning/oranye* hangat.
  - `emotion-sad`: UI berubah warna menjadi *glow biru gelap*.
  - `emotion-neutral`: Kembali ke warna bawaan ungu-cyan.
- **Transisi:** Menerapkan logika JS untuk menghapus *class* emosi lama dan menggantinya dengan yang baru setiap kali AI membalas.

---

### 2. Multi-Character Clash / Group Chat (Fitur 5)

Fitur ini membutuhkan antarmuka dan kontroler baru agar dua AI bisa berinteraksi di satu ruang obrolan.

#### [NEW] `app/Http/Controllers/ClashController.php`
- `setup()`: Menampilkan halaman untuk mencari dan memilih 2 karakter.
- `show($id1, $id2)`: Memuat lore kedua karakter dari database.
- `chat()`: Menerima pesan dari *user*, meracik **Prompt Ganda** di mana LLM diinstruksikan untuk memainkan peran kedua karakter sekaligus. Format *output* LLM JSON: 
  ```json
  {
    "char1": {"emotion": "...", "reply": "..."},
    "char2": {"emotion": "...", "reply": "..."}
  }
  ```
  *(Pendekatan ini jauh lebih cepat daripada memanggil API Groq dua kali berturut-turut).*

#### [NEW] `resources/views/clash/setup.blade.php`
- UI Sederhana untuk memilih 2 karakter. Terdiri dari dua kolom *search bar* (menggunakan fitur *Live Search* yang sama dengan `welcome.blade.php`). Setelah keduanya terpilih, tombol "Start Clash" akan aktif.

#### [NEW] `resources/views/clash/show.blade.php`
- **Layout 3 Kolom:** 
  - Kiri (25%): Lore & Gambar Karakter 1.
  - Kanan (25%): Lore & Gambar Karakter 2.
  - Tengah (50%): Arena Chatbox Grup.
- **Logika UI:** Menampilkan *bubble chat* Karakter 1 di sisi kiri *chatbox*, Karakter 2 di sisi kanan *chatbox*, dan *User* di tengah. Efek **Emosi Dinamis** juga diterapkan secara individual pada setiap sisi layar.

#### [MODIFY] `routes/web.php`
- Menambahkan *route* web untuk `/clash`, `/clash/{id1}/{id2}`, dan `/clash/{id1}/{id2}/chat`.

#### [MODIFY] `resources/views/welcome.blade.php`
- Menambahkan tombol menu "🔥 Enter Clash Arena" di navigasi atas agar user bisa mengakses fitur ini dari halaman utama.

---

## Verification Plan

### Manual Verification
1. **Tes Emosi:** Membuka karakter yang sudah ada (Luffy), lalu memancing emosinya (misal: "Saya membuang topi jerami milikmu"). Memastikan warna *border* dan *glow* berubah menjadi merah muda/marah.
2. **Tes Clash Arena:** Mengakses halaman Clash, mencari 2 karakter secara *live search*, lalu mengirim satu pesan. Memastikan kedua karakter merespons secara sinkron di *chatbox* dengan avatar yang berbeda.
