$Kumbia = new Object();
$Kumbia.app = "<?= urldecode($_REQUEST['app']) ?>";
$Kumbia.path = "<?= urldecode($_REQUEST['path']) ?>";
$Kumbia.module = "<?= urldecode($_REQUEST['module']) ?>";
$Kumbia.controller = "<?= $_REQUEST['controller'] ?>";
$Kumbia.action = "<?= $_REQUEST['action'] ?>";