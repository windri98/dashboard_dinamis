# API Dynamic File Upload Documentation

## Overview
Dynamic API sekarang mendukung upload file dan gambar dengan features:
- ✅ File upload (PDF, DOC, XLS, ZIP, dll)
- ✅ Image upload dengan thumbnail generation
- ✅ File validation (type, size)
- ✅ Automatic file metadata dalam response
- ✅ File cleanup saat update/delete

## File Types yang Didukung

### Images (Max 5MB)
- jpg, jpeg, png, gif, webp
- Auto-generate thumbnails (small, medium, large)

### Files (Max 10MB)  
- pdf, doc, docx, xls, xlsx, txt, zip, rar

## API Endpoints

### 1. Get Table Info
```
GET /api/dynamic/{table_name}/info
```
**Response:**
```json
{
    "success": true,
    "data": {
        "table_name": "dyn_master_data",
        "display_name": "Master Data",
        "columns": [
            {
                "name": "nama",
                "type": "string",
                "required": true
            },
            {
                "name": "foto",
                "type": "image", 
                "required": false
            },
            {
                "name": "dokumen",
                "type": "file",
                "required": false
            }
        ]
    }
}
```

### 2. Create Record dengan File
```
POST /api/dynamic/{table_name}
Content-Type: multipart/form-data
```

**Form Data:**
```
nama: "John Doe"
foto: [image file]
dokumen: [pdf file]
```

**Response:**
```json
{
    "success": true,
    "message": "Record created successfully",
    "data": {
        "id": 1,
        "nama": "John Doe",
        "foto": "uploads/dyn_master_data/foto/original/foto_20251011134530_abc123.jpg",
        "foto_meta": {
            "type": "image",
            "url": "/storage/uploads/dyn_master_data/foto/original/foto_20251011134530_abc123.jpg",
            "size": 245678,
            "thumbnails": {
                "small": "/storage/uploads/dyn_master_data/foto/thumbnails/small/small_foto_20251011134530_abc123.jpg",
                "medium": "/storage/uploads/dyn_master_data/foto/thumbnails/medium/medium_foto_20251011134530_abc123.jpg",
                "large": "/storage/uploads/dyn_master_data/foto/thumbnails/large/large_foto_20251011134530_abc123.jpg"
            }
        },
        "dokumen": "uploads/dyn_master_data/dokumen/document_20251011134530_xyz789.pdf",
        "dokumen_meta": {
            "type": "file",
            "url": "/storage/uploads/dyn_master_data/dokumen/document_20251011134530_xyz789.pdf",
            "size": 1024000,
            "extension": "pdf"
        }
    }
}
```

### 3. Update Record dengan File
```
PUT /api/dynamic/{table_name}/{id}
Content-Type: multipart/form-data
```
- File lama otomatis dihapus saat upload file baru
- Field yang tidak ada file akan tetap menggunakan file lama

### 4. Get Record dengan File Info
```
GET /api/dynamic/{table_name}/{id}
```
Response akan include metadata file otomatis.

## File Storage Structure
```
storage/app/public/uploads/{table_name}/{field_name}/
├── original/           # File asli
├── thumbnails/         # Thumbnail (untuk image)
│   ├── small/         # 150px
│   ├── medium/        # 300px
│   └── large/         # 600px
```

## Error Handling
```json
{
    "success": false,
    "message": "File upload error for foto: File type 'exe' not allowed. Allowed types: jpg, jpeg, png, gif, webp, pdf, doc, docx, xls, xlsx, txt, zip, rar"
}
```

## Security Features
- ✅ File type validation
- ✅ File size limits
- ✅ Secure filename generation
- ✅ Path traversal protection
- ✅ IP restriction tetap berlaku

## Frontend Implementation Example

### HTML Form
```html
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="nama" required>
    <input type="file" name="foto" accept="image/*">
    <input type="file" name="dokumen" accept=".pdf,.doc,.docx">
    <button type="submit">Upload</button>
</form>
```

### JavaScript dengan Fetch API
```javascript
const formData = new FormData();
formData.append('nama', 'John Doe');
formData.append('foto', fileInput.files[0]);

fetch('/api/dynamic/dyn_master_data', {
    method: 'POST',
    headers: {
        'X-Forwarded-For': '192.168.3.113' // IP whitelist
    },
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('File uploaded:', data.data);
        // Access image thumbnail
        console.log('Small thumbnail:', data.data.foto_meta.thumbnails.small);
    }
});
```