<?php
$extensionClassesPath = t3lib_extMgm::extPath('container', 'Classes/');
return array(
	'tx_container_classinfo' => $extensionClassesPath . 'ClassInfo.php',
	'tx_container_classinfocache' => $extensionClassesPath . 'ClassInfoCache.php',
	'tx_container_classinfofactory' => $extensionClassesPath . 'ClassInfoFactory.php',
	'tx_container_container' => $extensionClassesPath . 'Container.php',
);
?>