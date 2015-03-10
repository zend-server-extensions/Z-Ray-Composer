<?php
/*********************************
	Composer Z-Ray Extension
	Version: 1.00
**********************************/
namespace ZRay;

use ZRayExtension;

class Composer
{
    public function findFileWithExtensionExit($context, &$storage)
    {
        if ($context['returnValue'] === null) {
            // failed lookup, so class was not in Composer class map
            return;
        }

        $storage['classMap'][] = [
            'class'    => $context['functionArgs'][0],
            'filename' => str_replace(
                ['/', '\\'],
                DIRECTORY_SEPARATOR,
                $context['returnValue']
            )
        ];
    }


    public function registerExit($context, &$storage)
    {
    	$composerDir = dirname($context['calledFromFile']);
        $jsonFile    = $composerDir.'/installed.json';

        if (file_exists($jsonFile) || !is_readable($jsonFile)) {
            return false;
        }

        $json        = file_get_contents($jsonFile);
        $data        = json_decode($json);

        foreach ($data as $package) {
            $source = (isset($package->source) && isset($package->source->url))
                ? $package->source->url
                : '';

            $entry = [
                'name'    => $package->name,
                'version' => $package->version,
                'source'  => $source,
            ];

            $entry['requires'] = (empty($package->require) ? [] : (array) $package->require);

            if (! empty($package->authors)) {
                $entry['authors'] = $package->authors;
            }

            if (! empty($package->homepage)) {
                $entry['homepage'] = $package->homepage;
            }

            $storage['packages'][$package->name] = $entry;
        }
    }
}

$extension = new ZRayExtension("composer");
$composer  = new Composer();

$extension->setMetadata(array(
    'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
));

$extension->setEnabledAfter('Composer\Autoload\ClassLoader::register');
$extension->traceFunction(
    'Composer\Autoload\ClassLoader::register',
    function() {
    },
    [$composer, 'registerExit']
);
$extension->traceFunction(
    'Composer\Autoload\ClassLoader::findFileWithExtension',
    function() {
    },
    [$composer, 'findFileWithExtensionExit']
);
