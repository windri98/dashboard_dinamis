<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test File Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="file"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1>Test Dynamic API File Upload</h1>
    <p>Testing file upload functionality for dynamic API system</p>
    
    <form id="uploadForm">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="John Doe" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="john@example.com" required>
        </div>
        
        <div class="form-group">
            <label for="cv_upload">CV Upload (PDF/DOC/DOCX):</label>
            <input type="file" id="cv_upload" name="cv_upload" accept=".pdf,.doc,.docx">
            <small>Upload your CV file (max 2MB)</small>
        </div>
        
        <button type="submit">Upload Data with File</button>
    </form>
    
    <div id="result" class="result" style="display: none;"></div>

    <script>
        // Setup CSRF token for all AJAX requests
        document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('name', document.getElementById('name').value);
            formData.append('email', document.getElementById('email').value);
            
            const fileInput = document.getElementById('cv_upload');
            if (fileInput.files[0]) {
                formData.append('cv_upload', fileInput.files[0]);
            }
            
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.className = 'result';
            resultDiv.textContent = 'Uploading...';
            
            try {
                const response = await fetch('{{ url("/api/semua-data/store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    resultDiv.className = 'result success';
                    resultDiv.textContent = `✅ SUCCESS!\n\nStatus: ${response.status}\nData uploaded successfully!\n\nResponse:\n${JSON.stringify(result, null, 2)}`;
                } else {
                    resultDiv.className = 'result error';
                    resultDiv.textContent = `❌ ERROR!\n\nStatus: ${response.status}\nMessage: ${result.message || 'Unknown error'}\n\nResponse:\n${JSON.stringify(result, null, 2)}`;
                }
            } catch (error) {
                resultDiv.className = 'result error';
                resultDiv.textContent = `❌ NETWORK ERROR!\n\n${error.message}`;
            }
        });
    </script>
</body>
</html>