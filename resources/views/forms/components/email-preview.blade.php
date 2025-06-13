@props(['state'])

@php
    $emailTemplate = is_callable($email_template) ? $email_template() : $email_template ?? '';
    $siteName = is_callable($site_name) ? $site_name() : $site_name ?? config('app.name');
    $siteEmail = is_callable($email_from) ? $email_from() : $email_from ?? config('mail.from.address');

    // // Debug logging
    // Log::info('Email Template:', ['template' => $emailTemplate]);
    // Log::info('Site Name:', ['name' => $siteName]);
    // Log::info('Site Email:', ['email' => $siteEmail]);

    // Replace shortcodes with sample data
    $previewContent = str_replace(
        [
            '{{logo}}',
            '{{dark_logo}}',
            '{{site_name}}',
            '{{fullname}}',
            '{{message}}',
            '{{email}}',
            '{{site_url}}',
        ],
        [
            WhiteLogo(),
            DarkLogo(),
            $siteName,
            'John Doe',
            'This is a sample message to demonstrate how the email template will look with actual content.',
            $siteEmail,
            env('FRONTEND_URL', 'https://gigitright.com'),
        ],
        $emailTemplate,
    );

    // Log::info('Preview Content:', ['content' => $previewContent]);

    // Construct iframe HTML
    $iframeHtml = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                img { max-width: 100%; height: auto; }
                a { color: #2563eb; }
            </style>
        </head>
        <body>
            {$previewContent}
        </body>
        </html>
    HTML;
@endphp

<div class="mb-2">
    <h3 class="text-md font-medium text-white">Email Preview</h3>
</div>
<div class="p-4 bg-white rounded-lg shadow">
    <div class="mb-4">
        <p class="text-sm text-gray-500">This is how your email will look to users</p>
    </div>

    <div style="height: 200px" class="relative w-full h-[500px] border rounded-lg overflow-hidden">
        <iframe id="email-preview-frame" class="w-full h-full border-0"></iframe>

    </div>

    <div class="mt-4 text-sm text-gray-500">
        <p>Available shortcodes:</p>
        <ul class="list-disc list-inside">
            <li>{ fullname } - User's full name</li>
            <li>{ email } - User's email</li>
            <li>{ site_name } - Your site name</li>
            <li>{ message } - Email message content</li>
            <li>{ logo } - Site logo URL</li>
        </ul>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const frame = document.getElementById('email-preview-frame');
            if (frame) {
                const content = @json($iframeHtml);
                frame.srcdoc = content;
            }
        });

        // Reapply when Livewire updates
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', (message, component) => {
                const frame = document.getElementById('email-preview-frame');
                if (frame) {
                    const content = @json($iframeHtml);
                    frame.srcdoc = content;
                }
            });
        });
    </script>
@endpush
