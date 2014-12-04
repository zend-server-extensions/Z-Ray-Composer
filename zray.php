<?php

namespace ZRay;

class Composer {
	public function findFileWithExtensionExit($context, &$storage)
	{
		$storage['classMap'][] = array(
						'class' => $context['functionArgs'][0],
						'filename' => str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $context['returnValue']));
	}


	public function registerExit($context, &$storage)
	{
		$composerDir = dirname($context['calledFromFile']);
		$json = file_get_contents($composerDir.'/installed.json');
		$data = json_decode($json);
		foreach ($data as $package) {
			$entry = [
				'name'    => $package->name,
				'version' => $package->version,
				'source'  => (isset($package->source) && isset($package->source->url)) ? $package->source->url : '',
			];
			$entry['requires'] = (empty($package->require) ? array() : (array) $package->require);
			if (!empty($package->authors)) {
				$entry['authors'] = $package->authors;
			}
			if (!empty($package->homepage)) {
				$entry['homepage'] = $package->homepage;
			}
			$storage['packages'][$package->name] = $entry;
		}
	}
}

$zre = new \ZRayExtension("composer");

$zrayComposer = new Composer();

$zre->setMetadata(array(
	'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
));

$zre->setEnabledAfter('Composer\Autoload\ClassLoader::register');
$zre->traceFunction('Composer\Autoload\ClassLoader::register', function(){}, array($zrayComposer, 'registerExit'));
$zre->traceFunction('Composer\Autoload\ClassLoader::findFileWithExtension', function(){}, array($zrayComposer, 'findFileWithExtensionExit'));
