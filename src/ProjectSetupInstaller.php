<?php

namespace Codingwithrk\RkProjectSetup;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;

class ProjectSetupInstaller implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'handlePostInstall',
        ];
    }

    public function handlePostInstall(PackageEvent $event)
    {
        $io = $event->getIO();

        // Run npm install
        $io->write("<info>📦 Running npm install...</info>");
        shell_exec("npm install");
        $io->write("<comment>✅ npm install completed!</comment>");

        // Install Livewire
        $io->write("<info>📦 Installing Livewire...</info>");
        shell_exec("composer require livewire/livewire");
        $io->write("<comment>✅ Livewire installed!</comment>");

        // Publish Livewire config
        $io->write("<info>⚙️  Publishing Livewire config...</info>");
        shell_exec("php artisan livewire:publish --config");
        $io->write("<comment>✅ Livewire config published!</comment>");

        // Generate Livewire layout
        $io->write("<info>🧱 Generating Livewire layout...</info>");
        shell_exec("php artisan livewire:layout");
        $io->write("<comment>✅ Layout generated!</comment>");

        // Install Flux
        $io->write("<info>📦 Installing Flux...</info>");
        shell_exec("composer require livewire/flux");
        $io->write("<comment>✅ Flux installed!</comment>");

        // Modify layout file
        $layout = 'resources/views/components/layouts/app.blade.php';
        if (file_exists($layout)) {
            $io->write("<info>✏️  Updating layout file...</info>");
            file_put_contents($layout, <<<BLADE
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ \$title ?? 'Page Title' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body>
    {{ \$slot }}
    @fluxScripts
</body>
</html>
BLADE
            );
            $io->write("<comment>✅ Layout updated!</comment>");
        }

        // Modify app.css
        $css = 'resources/css/app.css';
        if (file_exists($css)) {
            $io->write("<info>🎨 Updating app.css file...</info>");
            file_put_contents($css, <<<CSS
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

@custom-variant dark (&:where(.dark, .dark *));
@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
    'Segoe UI Symbol', 'Noto Color Emoji';
}
CSS
            );
            $io->write("<comment>✅ app.css updated!</comment>");
        }

        $io->write("<info>🎉 rk-project-setup completed successfully!</info>");
    }

}