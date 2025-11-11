<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Preview - {{ $template->name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .header { 
            background: #4f46e5; 
            color: white; 
            padding: 20px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .subject { 
            background: #f8fafc; 
            padding: 15px; 
            border-bottom: 1px solid #e2e8f0; 
            font-weight: bold;
        }
        .tabs { 
            display: flex; 
            border-bottom: 1px solid #e2e8f0; 
        }
        .tab { 
            padding: 10px 20px; 
            cursor: pointer; 
            border-bottom: 2px solid transparent; 
            background: #f8fafc;
        }
        .tab.active { 
            border-bottom-color: #4f46e5; 
            color: #4f46e5; 
            background: white;
        }
        .tab-content { 
            display: none; 
            padding: 20px; 
            min-height: 400px;
        }
        .tab-content.active { 
            display: block; 
        }
        pre { 
            white-space: pre-wrap; 
            font-family: monospace; 
            margin: 0;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $template->name }} - Email Preview</h1>
            <div class="actions">
                <a href="{{ route('admin.email-templates.edit', $template->id) }}" class="btn btn-secondary">Edit</a>
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        <div class="subject">
            Subject: {{ $rendered['subject'] }}
        </div>
        <div class="tabs">
            <div class="tab active" onclick="showTab('html')">HTML Preview</div>
            <div class="tab" onclick="showTab('text')">Plain Text</div>
        </div>
        <div id="html-tab" class="tab-content active">
            {!! $rendered['body_html'] !!}
        </div>
        <div id="text-tab" class="tab-content">
            <pre>{{ $rendered['body_text'] }}</pre>
        </div>
    </div>
    
    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }
    </script>
</body>
</html>