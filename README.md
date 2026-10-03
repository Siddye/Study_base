# 📚 StudyBase — Personal Study Knowledge Base & RAG AI Assistant

> *"Jangan pusing lagi mencari jawaban di tumpukan 50 PDF kuliah. Upload semua materimu, tanyakan apa saja, dan pelajari semuanya dari satu tempat dengan rujukan halaman yang akurat."*

---

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12" />
  <img src="https://img.shields.io/badge/Google_Gemini-RAG_AI-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Gemini AI" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Vector_Search-Cosine_Similarity-6366F1?style=for-the-badge" alt="Vector Search" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-Modern_Dark_UI-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind" />
</p>

---

## 💡 Apa Itu StudyBase?

**StudyBase** adalah aplikasi perpustakaan digital cerdas (*Personal Knowledge Hub*) yang dirancang khusus untuk mahasiswa dan pelajar. Berbeda dari tools "Chat with PDF" biasa yang hanya bisa membaca satu file sekali pakai, StudyBase memahami **seluruh koleksi dokumen kuliah Anda** secara terpusat (slide presentasi dosen, modul praktikum, e-book, jurnal, maupun rangkuman DOCX/PDF).

Menggunakan teknologi **RAG (Retrieval-Augmented Generation)** dan **Vector Semantic Search**, StudyBase memungkinkan Anda mencari konsep dan bertanya apa pun kepada AI, di mana AI **hanya akan menjawab berdasarkan dokumen materi Anda** lengkap dengan **sitasi nomor halaman sumber** yang dapat diverifikasi secara instan.

---

## 🌟 Mengapa Ini Sangat Mengubah Hidup Mahasiswa & Pelajar?

Kehidupan sebagai pelajar atau mahasiswa sering kali dipenuhi tantangan seperti:
- Menumpuknya ratusan file PDF dari puluhan mata kuliah dan semester yang berbeda.
- Susahnya mencari satu rumus atau definisi spesifik saat butuh cepat (fitur `Ctrl + F` biasa sering gagal jika istilah kata kuncinya berbeda sedikit).
- AI umum (seperti ChatGPT standar) sering melakukan **halusinasi** atau memberikan jawaban di luar silabus kurikulum dosen.

### 🔥 Solusi yang Ditawarkan StudyBase:

| Fitur Unggulan | Manfaat Nyata untuk Pelajar |
| :--- | :--- |
| 🔍 **Semantic Vector Search** | Mencari materi berdasarkan **makna dan konteks**, bukan cuma kecocokan kata kunci huruf demi huruf. |
| 🎯 **Zero Hallucination with Page Citations** | Setiap penjelasan AI selalu mencantumkan sumber jelas: `[Nama Dokumen, Hal. X]`. Tidak ada lagi jawaban mengarang. |
| 📁 **Struktur Folder Semester & Matkul** | Mengelompokkan materi per semester atau mata kuliah dengan label warna tag modern. |
| 🧠 **Mode Belajar Cerdas (5-in-1 Study Modes)** | Sesuaikan cara belajar sesuai kebutuhan: tanya jawab langsung, analogi sederhana, ringkasan instan, kuis, atau kartu hafalan. |
| ⚡ **Cross-Document Reasoning** | Menghubungkan informasi dari beberapa slide dan bab materi sekaligus dalam satu jawaban utuh. |

---

## 🎓 Bagaimana Mahasiswa & Pelajar Memanfaatkannya?

Berikut adalah contoh skenario penggunaan nyata sehari-hari:

### 1. 🚀 Belajar Cepat H-1 Ujian (SKS — Sistem Kebut Semalam)
- **Tindakan**: Buka folder mata kuliah terkait, lalu pilih mode **`📝 Summarize`**.
- **Hasil**: AI merangkum konsep kunci, rumus, dan kesimpulan dari seluruh slide kuliah pertemuan 1 hingga 14 dalam hitungan detik.

### 2. 🧩 Memahami Materi Dosen yang Terlalu Rumit
- **Tindakan**: Tanya konsep yang membingungkan dengan mode **`🧠 Explain`** (contoh: *"Jelaskan algoritma Dijkstra dan analoginya dalam kehidupan sehari-hari"*).
- **Hasil**: AI menerjemahkan bahasa teks buku yang kaku menjadi penjelasan intuitif dengan analogi yang ramah mahasiswa, tetap berlandaskan modul kuliah Anda.

### 3. ❓ Latihan & Simulasi Ujian Mandiri
- **Tindakan**: Klik mode **`❓ Quiz Me`** (contoh: *"Buatkan 5 soal pilihan ganda tentang Normalisasi Database"*).
- **Hasil**: AI membuatkan soal-soal latihan beserta kunci jawaban, pembahasan, dan rujukan nomor halamannya untuk menguji kesiapan belajar Anda.

### 4. 🃏 Menghafal Istilah & Definisi Penting
- **Tindakan**: Klik mode **`🃏 Flashcards`**.
- **Hasil**: AI mengekstrak definisi, formula, dan poin penting menjadi format kartu hafalan tanya-jawab yang siap diulang.

---

## 🏗️ Arsitektur & Alur Sistem (RAG Pipeline)

```mermaid
flowchart TD
    A[📄 Upload PDF / DOCX Kuliah] --> B[⚙️ Text Extraction per Halaman\nsmalot/pdfparser & PhpWord]
    B --> C[✂️ Chunking Teks ~350 Kata\nSliding Window with Overlap]
    C --> D[🔮 Google Gemini Embedding API\ngemini-embedding-001]
    D --> E[(💾 MySQL Database\nSimpan Content & Vector JSON)]
    
    F[💬 Mahasiswa Mengajukan Pertanyaan] --> G[🔮 Vectorize Pertanyaan]
    G --> H[📐 Cosine Similarity Vector Search\nCari Chunks Paling Relevan]
    E -. Mengambil Chunks .-> H
    
    H --> I[🧠 RAG Prompt Builder\nKonteks Dokumen + Sitasi Halaman]
    I --> J[🤖 LLM Gemini 3.8 Flash]
    J --> K[✨ Jawaban Terstruktur + Sitasi [Dokumen, Hal. X]]
```

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade + Tailwind CSS (Aksen Modern Glassmorphism & Dark Mode) + Lucide Icons
- **Database**: MySQL (Disimpan dalam format vektor JSON terindeks)
- **Ekstraksi Dokumen**: `smalot/pdfparser` (PDF per halaman), `phpoffice/phpword` (DOCX)
- **Vector Search Engine**: Pure PHP Cosine Similarity Vector Search
- **AI Provider**: Google Gemini API (`gemini-embedding-001` untuk Vektor & `gemini-3.8-flash` untuk LLM)

---

## 🚀 Panduan Instalasi & Menjalankan Project

Ikuti langkah mudah berikut untuk menjalankan StudyBase di komputer lokal Anda:

### 1. Prasyarat Sistem
Pastikan telah terpasang di perangkat Anda:
- **PHP 8.2+** (dengan ekstensi `pdo_mysql`, `curl`, `mbstring`, `zip`)
- **XAMPP / Laragon** (untuk web server Apache & database MySQL)
- **Composer**
- **Node.js & NPM**
- **Google AI Studio API Key** (Tersedia tier gratis di [Google AI Studio](https://aistudio.google.com/))

---

### 2. Clone Repository & Install Dependency
Buka terminal / Command Prompt:
```bash
git clone https://github.com/username/study-knowledge-base.git
cd study-knowledge-base

# Install dependency PHP
composer install

# Install dependency Frontend
npm install
```

---

### 3. Konfigurasi Database & Environment
1. Buat database baru di MySQL (via phpMyAdmin / Laragon):
   ```sql
   CREATE DATABASE study_knowledge_base;
   ```

2. Salin file environment:
   ```bash
   copy .env.example .env
   ```

3. Buka file `.env` dan sesuaikan pengaturannya:
   ```env
   APP_NAME="Study Knowledge Base"
   APP_URL=http://localhost:8000

   # Konfigurasi Database MySQL
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=study_knowledge_base
   DB_USERNAME=root
   DB_PASSWORD=

   # Konfigurasi AI Provider (Google Gemini)
   AI_PROVIDER=gemini
   GEMINI_API_KEY=masukkan_api_key_gemini_anda_disini
   GEMINI_EMBEDDING_MODEL=gemini-embedding-001
   GEMINI_CHAT_MODEL=gemini-3.8-flash
   ```

4. Generate Application Key & Symlink Storage:
   ```bash
   php artisan key:generate
   php artisan storage:link
   ```

---

### 4. Jalankan Migration & Seeder Demo
Jalankan perintah berikut untuk membuat seluruh tabel database dan mengisikan akun demo:
```bash
php artisan migrate --seed
```

> 👤 **Akun Demo Bawaan:**
> - **Email**: `mahasiswa@kampus.ac.id`
> - **Password**: `password123`
> *(Atau Anda bisa langsung mendaftar akun baru melalui tombol Register di web)*

---

### 5. Jalankan Server Aplikasi
Jalankan web server lokal Laravel:
```bash
php artisan serve
```

Buka browser Anda dan akses:
👉 **`http://127.0.0.1:8000`**

---

## 📖 Panduan Penggunaan Singkat

1. **Login** menggunakan akun Anda atau akun demo.
2. Masuk ke menu **Library & Materi**:
   - Buat folder untuk semester atau nama mata kuliah Anda.
   - Upload materi kuliah berformat **PDF** atau **DOCX**.
   - Sistem secara otomatis akan memecah teks per halaman dan menghitung vektor embedding.
3. Gunakan **Semantic Search** untuk mencari potongan penjelasan spesifik beserta indikator skor relevansi (*Match %*).
4. Masuk ke menu **Ask Your AI**:
   - Buat sesi tanya jawab baru.
   - Pilih mode belajar (**💬 Tanya Biasa**, **🧠 Explain**, **📝 Summarize**, **❓ Quiz Me**, atau **🃏 Flashcards**).
   - Tanyakan apa pun dan perhatikan bagaimana AI menjawab dengan rujukan nomor halaman materi asli Anda!

---

## ⚡ Perintah Berguna (Artisan Commands)

- **Memproses Ulang Vektor Dokumen**:
  Jika Anda mengganti model AI atau ingin memproses ulang seluruh dokumen yang ada:
  ```bash
  php artisan documents:reprocess
  ```
- **Menguji Alur RAG via Terminal**:
  ```bash
  php artisan test:rag "Jelaskan tentang materi bangun datar"
  ```

---

## 📜 Lisensi & Kontribusi

Proyek ini dibangun sebagai platform open learning untuk membantu mahasiswa dan pelajar di seluruh Indonesia belajar lebih efisien dengan bantuan teknologi AI terdesentralisasi dan terverifikasi.

Selamat Belajar & Sukses Selalu! 🎓✨
