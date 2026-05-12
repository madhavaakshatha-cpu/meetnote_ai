# MeetNote AI - Smart Meeting & Classroom Notes Generator

![MeetNote AI](https://img.shields.io/badge/MeetNote-AI-blue?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple?style=flat-square)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0+-orange?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## 📋 Overview

**MeetNote AI** is an intelligent meeting and classroom notes generator that transforms audio and video recordings into comprehensive, well-organized notes with summaries, key points, and enhanced formatting.

### ✨ Key Features

- 🎤 **Audio/Video Upload** - Upload MP3, WAV, MP4, MKV, and more (supports files >1GB)
- 🤖 **AI Transcription** - Powered by OpenAI Whisper API
- 📝 **Smart Summaries** - Automatically generate concise meeting summaries
- ⭐ **Key Points Extraction** - Highlight important discussion points
- 📄 **Note Generation** - Formatted, structured notes ready to use
- 🔊 **Noise Removal** - Clean background noise from recordings
- 🌍 **Multilingual Support** - 15+ languages supported (auto-detect available)
- ⚡ **Fast Processing** - Optimized for large files with chunked processing
- 📥 **Export Options** - Download as PDF, TXT, or JSON
- 📱 **Responsive Design** - Works seamlessly on desktop, tablet, and mobile

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 7.4+ |
| **Frontend** | HTML5, CSS3, Bootstrap 5 |
| **JavaScript** | Vanilla JavaScript (ES6+) |
| **Audio Processing** | FFmpeg |
| **AI Engine** | OpenAI Whisper API |
| **Server** | Apache/Nginx with PHP |

---

## 📦 Project Structure

```
meetnote_ai/
├── public/
│   ├── index.php              # Main entry point
│   ├── dashboard.php          # User interface
│   ├── css/
│   │   ├── style.css          # Custom styling
│   │   └── responsive.css     # Mobile responsive
│   └── js/
│       ├── main.js            # Core utilities
│       ├── upload.js          # File upload handling
│       └── display.js         # Results display
├── src/
│   ├── FileHandler.php        # File management
│   ├── AudioProcessor.php     # FFmpeg processing
│   ├── WhisperAPI.php         # Whisper integration
│   └── NLPProcessor.php       # Text processing
├── api/
│   ├── upload.php             # Upload endpoint
│   ├── process.php            # Processing endpoint
│   └── results.php            # Results endpoint
├── config/
│   └── constants.php          # Application constants
├── docs/
│   ├── INSTALLATION.md        # Setup guide
│   ├── API.md                 # API documentation
│   └── CONFIGURATION.md       # Configuration guide
├── uploads/                   # Uploaded files (temp)
├── results/                   # Processing results
├── logs/                      # Application logs
├── .env.example               # Environment template
├── .gitignore                 # Git exclusions
└── composer.json              # PHP dependencies

```

---

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- FFmpeg installed
- OpenAI API key
- Composer (optional but recommended)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/madhavaakshatha-cpu/meetnote_ai.git
   cd meetnote_ai
   ```

2. **Setup environment**
   ```bash
   cp .env.example .env
   # Edit .env and add your OpenAI API key
   ```

3. **Create required directories**
   ```bash
   mkdir -p uploads/ temp/ results/ logs/
   chmod 755 uploads/ temp/ results/ logs/
   ```

4. **Install dependencies** (optional)
   ```bash
   composer install
   ```

5. **Start development server**
   ```bash
   php -S localhost:8000 -t public
   ```

6. **Open in browser**
   ```
   http://localhost:8000/dashboard.php
   ```

---

## 📖 Documentation

- **[Installation Guide](docs/INSTALLATION.md)** - Complete setup instructions for different OS
- **[API Documentation](docs/API.md)** - Full API endpoints and examples
- **[Configuration Guide](docs/CONFIGURATION.md)** - Detailed configuration options

---

## 🔌 API Endpoints

### Upload File
```
POST /api/upload.php
Content-Type: multipart/form-data

Parameters:
- file: Audio/Video file (required)
- language: Audio language (optional, default: auto)

Response:
{
    "success": true,
    "file_id": "meetnote_1234567890",
    "filename": "meeting.mp3",
    "size": 5242880
}
```

### Process File
```
POST /api/process.php
Content-Type: application/x-www-form-urlencoded

Parameters:
- file_id: File ID from upload (required)
- language: Audio language (optional)
- remove_noise: Remove background noise (0 or 1)
- highlight_points: Highlight key points (0 or 1)

Response:
{
    "success": true,
    "data": {
        "transcription": "...",
        "summary": "...",
        "key_points": ["Point 1", "Point 2"],
        "notes": "..."
    }
}
```

### Get Results
```
GET /api/results.php?file_id=meetnote_1234567890

Response:
{
    "success": true,
    "data": {
        "file_id": "meetnote_1234567890",
        "transcription": "...",
        "summary": "...",
        "key_points": [...],
        "notes": "..."
    }
}
```

---

## 🌍 Supported Languages

English (en), Spanish (es), French (fr), German (de), Chinese Mandarin (zh), Japanese (ja), Hindi (hi), Portuguese (pt), Arabic (ar), Russian (ru), Korean (ko), and more with auto-detection support.

---

## 📊 Supported File Formats

### Audio
- MP3, WAV, FLAC, OGG, AAC, M4A

### Video
- MP4, MKV, AVI, MOV, WMV, FLV

### File Size
- Single file: Up to 5GB
- Chunked upload: Automatic for files >100MB

---

## ⚙️ Configuration

All configuration is handled through the `.env` file:

```env
# OpenAI Configuration
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_MODEL=whisper-1

# FFmpeg Path
FFMPEG_PATH=/usr/bin/ffmpeg

# File Upload Settings
MAX_FILE_SIZE=5368709120  # 5GB in bytes
UPLOAD_DIR=uploads/
TEMP_DIR=temp/
RESULTS_DIR=results/

# Processing Settings
PROCESS_TIMEOUT=3600      # 1 hour
REMOVE_NOISE=1            # 0 or 1
HIGHLIGHT_POINTS=1       # 0 or 1

# Logging
LOG_DIR=logs/
LOG_LEVEL=info
```

---

## 🔐 Security

- ✅ File type validation
- ✅ File size limits
- ✅ Secure file storage
- ✅ Input sanitization
- ✅ CORS headers configured
- ✅ API rate limiting ready

---

## 📝 Usage Example

### Via Web UI

1. Open `http://localhost:8000/dashboard.php`
2. Select language (auto-detect or manual)
3. Drag & drop or click to upload audio/video
4. Choose processing options (noise removal, highlight points)
5. Click "Process"
6. View and download results (PDF, TXT, JSON)

### Via API

```bash
# Upload file
curl -X POST -F "file=@meeting.mp3" \
  http://localhost:8000/api/upload.php

# Process file
curl -X POST -d "file_id=meetnote_xyz&language=en&remove_noise=1" \
  http://localhost:8000/api/process.php

# Get results
curl "http://localhost:8000/api/results.php?file_id=meetnote_xyz"
```

---

## 🐛 Troubleshooting

### FFmpeg not found
```bash
# Linux
sudo apt-get install ffmpeg

# macOS
brew install ffmpeg

# Windows
# Download from https://ffmpeg.org/download.html
```

### OpenAI API errors
- Verify API key is correct in `.env`
- Check API key has sufficient credits
- Ensure file is not corrupted

### File upload errors
- Check `uploads/` directory permissions (chmod 755)
- Verify sufficient disk space
- Check max upload size in PHP config

---

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 👨‍💻 Author

**Madhava Akshatha**
- GitHub: [@madhavaakshatha-cpu](https://github.com/madhavaakshatha-cpu)
- Email: madhavaakshatha@gmail.com

---

## 🙏 Acknowledgments

- [OpenAI Whisper](https://openai.com/research/whisper) - For transcription API
- [Bootstrap](https://getbootstrap.com/) - For responsive UI framework
- [FFmpeg](https://ffmpeg.org/) - For audio/video processing

---

## 📮 Support

For issues, feature requests, or questions:
- GitHub Issues: https://github.com/madhavaakshatha-cpu/meetnote_ai/issues
- Email: madhavaakshatha@gmail.com

---

## 🎉 Version History

### v1.0.0 (2026-05-12)
- Initial release
- Core transcription feature
- Summary generation
- Key points extraction
- Responsive web UI
- API endpoints

---

**Made with ❤️ by Madhava Akshatha**
