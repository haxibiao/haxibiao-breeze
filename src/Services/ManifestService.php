<?php

namespace Haxibiao\Breeze\Services;

class ManifestService
{
    public function generate()
    {
        //尊重 seo_site_name() 定义pwa的name
        $basicManifest = [
            'name'             => seo_site_name() ?? config('breeze.pwa.manifest.name'),
            'short_name'       => seo_site_name() ?? config('breeze.pwa.manifest.short_name'),
            'start_url'        => asset(config('breeze.pwa.manifest.start_url')),
            'display'          => config('breeze.pwa.manifest.display'),
            'theme_color'      => config('breeze.pwa.manifest.theme_color'),
            'background_color' => config('breeze.pwa.manifest.background_color'),
            'orientation'      => config('breeze.pwa.manifest.orientation'),
            'status_bar'       => config('breeze.pwa.manifest.status_bar'),
            'splash'           => config('breeze.pwa.manifest.splash'),
        ];

        foreach (config('breeze.pwa.manifest.icons') as $size => $file) {
            $fileInfo                 = pathinfo($file['path']);
            $basicManifest['icons'][] = [
                'src'     => $file['path'],
                'type'    => 'image/' . $fileInfo['extension'],
                'sizes'   => (isset($file['sizes'])) ? $file['sizes'] : $size,
                'purpose' => $file['purpose'],
            ];
        }

        if (config('breeze.pwa.manifest.shortcuts')) {
            foreach (config('breeze.pwa.manifest.shortcuts') as $shortcut) {

                if (array_key_exists("icons", $shortcut)) {
                    $fileInfo = pathinfo($shortcut['icons']['src']);
                    $icon     = [
                        'src'     => $shortcut['icons']['src'],
                        'type'    => 'image/' . $fileInfo['extension'],
                        'purpose' => $shortcut['icons']['purpose'],
                    ];
                    if (isset($shortcut['icons']['sizes'])) {
                        $icon["sizes"] = $shortcut['icons']['sizes'];
                    }
                } else {
                    $icon = [];
                }

                $basicManifest['shortcuts'][] = [
                    'name'        => trans($shortcut['name']),
                    'description' => trans($shortcut['description']),
                    'url'         => $shortcut['url'],
                    'icons'       => [
                        $icon,
                    ],
                ];
            }
        }

        foreach (config('breeze.pwa.manifest.custom') as $tag => $value) {
            $basicManifest[$tag] = $value;
        }
        return $basicManifest;
    }

}
