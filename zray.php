<?php

namespace ZRay;


class Composer {
	private $zre = null;

	public function setZRE($zre) {
		$this->zre = $zre;
	}

	public function findFileWithExtensionExit($context, &$storage)
	{
		$storage['composerClassMap'][] = array(
						'class' => $context['functionArgs'][0],
						//'extension' => $context['functionArgs'][1],
						'filename' => $context['returnValue']);
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
				'source'  => $package->source->url,
			//	'requires' => (array) $package->require,
			];
			$entry['requires'] = (empty($package->require) ? array() : (array) $package->require);
			if (!empty($package->authors)) {
				$entry['authors'] = $package->authors;
			}
			if (!empty($package->homepage)) {
				$entry['homepage'] = $package->homepage;
			}
			$storage['composerPackages'][$package->name] = $entry;
		}
	}

}

$zre = new \ZRayExtension("composer");

$zrayComposer = new Composer();
$zrayComposer->setZRE($zre);

$zre->setMetadata(array(
	'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
));

$zre->setEnabledAfter('Composer\Autoload\ClassLoader::register');
$zre->traceFunction('Composer\Autoload\ClassLoader::register', function(){}, array($zrayComposer, 'registerExit'));
$zre->traceFunction('Composer\Autoload\ClassLoader::findFileWithExtension', function(){}, array($zrayComposer, 'findFileWithExtensionExit'));
