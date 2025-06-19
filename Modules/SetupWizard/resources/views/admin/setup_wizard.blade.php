<!DOCTYPE html>
<html <?php print lang_attributes(); ?>>
<head>
    <title><?php _e('Setup Wizard'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex">
    <?php get_favicon_tag(); ?>

    <?php print mw_header_scripts() ?>


    <link type="text/css" rel="stylesheet" media="all"
          href="<?php print asset('vendor/microweber-packages/microweber-filament-theme/build/microweber-filament-theme.css'); ?>"/>
    <link type="text/css" rel="stylesheet" media="all"
          href="<?php print asset('vendor/microweber-packages/frontend-assets/build/install.css'); ?>"/>

</head>

<body>
<main class="w-100 h-100vh">
    <link href="//fonts.googleapis.com/css?family=Inter:200,300,400,500,600,700,800,900" rel="stylesheet"/>

    <div class="templates-wrapper">
        <div class="templates-header">
            <h2>Choose a template</h2>
            <div class="templates-filters">
                <select class="form-select" onchange="filterTemplates(this.value)">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="templates-container">
            @foreach($templates as $template)
                <div class="template-item" data-categories="{{ json_encode($template['categories']) }}">
                    <div class="template-preview" style="background-image: url('{{ $template['screenshot'] ?? '' }}'); background-color: {{ empty($template['screenshot']) ? '#e5e7eb' : 'transparent' }};">
                    </div>
                    <div class="template-info">
                        <h3>{{ $template['name'] }}</h3>
                        @if(isset($template['description']))
                            <div class="template-description d-none">
                                <p>{{ $template['description'] }}</p>
                            </div>
                        @endif


                        <button class="btn btn-primary" onclick="installTemplate('{{ $template['dir_name'] }}')">
                            Use this template
                        </button>
                    </div>



                </div>
            @endforeach
        </div>
    </div>

    <script>
        function filterTemplates(category) {
            const items = document.querySelectorAll('.template-item');
            items.forEach(item => {
                const categories = JSON.parse(item.dataset.categories);
                item.style.display = !category || categories.includes(category) ? 'block' : 'none';
            });
        }

        function installTemplate(template) {
            mw.spinner({element: document.body, message: 'Installing template...'}).show();

            fetch("{{ route('admin.setup-wizard.install-template') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({template: template})
            })
                .then(response => response.json())
                .then(data => {
                    mw.spinner({element: document.body}).hide();
                    if (data.error) {
                        mw.notification.error(data.error);
                    } else if (data.success) {
                        mw.notification.success(data.success);
                        window.location.href = "{{ admin_url() }}";
                    }
                })
                .catch(error => {
                    mw.spinner({element: document.body}).hide();
                    mw.notification.error('Error installing template');
                });
        }
    </script>

    <style>
        .templates-wrapper {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .templates-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .templates-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .template-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .template-item:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .template-preview {
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            transition: background-position 3s ease-in-out;
        }

        .template-preview:hover {
            background-position: bottom center;
        }

        .template-info {
            padding: 15px;
        }

        .template-info h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
        }
    </style>
</main>

<?php print mw_footer_scripts() ?>

</body>
</html>
