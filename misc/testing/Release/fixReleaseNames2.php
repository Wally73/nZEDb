<?php
/*
* This script will rename all releases posted by 'nonscene@ef.net' to the extracted filename within the rar.
* It will then reset all the values, so PostProcessing will check the release again and download all relevant info
* Written by stephenl03
* updated by Fossil01 and ThePeePs
*/

require_once realpath(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'bootstrap.php');

use nzedb\db\DB;
use nzedb\Categorize;
use nzedb\Category;
use nzedb\ColorCLI;
use nzedb\ConsoleTools;
use nzedb\Groups;
use nzedb\NameFixer;

$pdo = new DB();
$cat = new Categorize(['Settings' => $pdo]);
$pdo->log = new ColorCLI();
$consoletools = new ConsoleTools(['ColorCLI' => $pdo->log]);
$fixer = new NameFixer();

$poster = "nonscene@Ef.net (EF)";
$regex = "^.*[0-9]{6}-.*$";
$count = 0;

$timestart = TIME();
echo $pdo->log->header("Getting releases to process...");
$sql = sprintf("SELECT rf.name AS textstring, rel.name, rel.searchname, rel.searchname, rel.groups_id, rel.categories_id, rel.id AS releases_id FROM releases rel INNER JOIN release_files rf ON (rf.releases_id = rel.id) WHERE nzbstatus = 1 AND predb_id = 0 AND fromname = %s AND searchname REGEXP %s;", $pdo->escapeString($poster), $pdo->escapeString($regex));
$result = $pdo->query($sql);
$cats = new Category();
$groups = new Groups();

foreach ($result as $r) {
  $rts = $r['textstring'];
  $rts = preg_replace("#\.[a-z0-9]{2,4}$#", "", $rts);

  $searchname = sprintf("UPDATE releases SET searchname = %s WHERE id = %d;", $pdo->escapeString($rts), $r['releases_id'], $r['releases_id']);
  $releases = sprintf("UPDATE releases SET consoleinfo_id = NULL, gamesinfo_id = 0, imdbid = NULL, musicinfo_id = NULL, bookinfo_id = NULL, videos_id = 0, tv_episodes_id = 0, xxxinfo_id = 0, passwordstatus = -1, haspreview = -1, jpgstatus = 0, videostatus = 0, audiostatus = 0, nfostatus = -1 WHERE id = %d", $r['releases_id']);
  $resp = $pdo->queryExec($searchname);
  $res = $pdo->queryExec($releases);
  $catId = $cat->determineCategory($r['groups_id'], $rts);
  if ($r['categories_id'] != $catId) {
    $catup = $pdo->queryExec(sprintf("UPDATE releases SET iscategorized = 1,	categories_id = %d WHERE id = %d",	$catId,	$r['releases_id']));
  }
  $echodata = array('new_name' => $rts, 'old_name' => $r['searchname'], 'old_category' => $cats->getNameByID($r['categories_id']), 'new_category' => $cats->getNameByID($catId), 'group' => $groups->getNameByID($r['groups_id']), 'release_id' => $r['releases_id'], 'method' => 'File Name Match');
  $fixer->echoChangedReleaseName($echodata);
  if ($resp && $res && $catup) {
    echo $pdo->log->info("Done!");
  } else {
    var_dump($res);
  }
  $count++;
}

$poster = "yEncBin@Poster.com (yEncBin)";
$regex = "[0-9]{2}[Ee][0-9]{2}[Ss].*\.mkv$";
$regex2 = "^[A-Za-z0-9]*$";

$sql = sprintf("SELECT rf.name AS textstring, rel.name, rel.searchname, rel.id AS releases_id FROM releases rel INNER JOIN release_files rf ON (rf.releases_id = rel.id) WHERE nzbstatus = 1 AND predb_id = 0 AND fromname = %s AND rf.name REGEXP %s AND searchname REGEXP %s;", $pdo->escapeString($poster), $pdo->escapeString($regex), $pdo->escapeString($regex2));
$result = $pdo->query($sql);

foreach ($result as $r) {
  $rts = $r['textstring'];
  $rts = preg_replace("#\.mkv$#", "", $rts);
  $rts = explode("\\", $rts);
  $rts = strrev($rts[1]);

  $searchname = sprintf("UPDATE releases SET searchname = %s WHERE id = %d;", $pdo->escapeString($rts), $r['releases_id'], $r['releases_id']);
  $releases = sprintf("UPDATE releases SET consoleinfo_id = NULL, gamesinfo_id = 0, imdbid = NULL, musicinfo_id = NULL, bookinfo_id = NULL, videos_id = 0, tv_episodes_id = 0, xxxinfo_id = 0, passwordstatus = -1, haspreview = -1, jpgstatus = 0, videostatus = 0, audiostatus = 0, nfostatus = -1 WHERE id = %d", $r['releases_id']);
  $resp = $pdo->queryExec($searchname);
  $res = $pdo->queryExec($releases);
  $catId = $cat->determineCategory($r['groups_id'], $rts);
  if ($r['categories_id'] != $catId) {
    $catup = $pdo->queryExec(sprintf("UPDATE releases SET iscategorized = 1,	categories_id = %d WHERE id = %d",	$catId,	$r['releases_id']));
  }
  $echodata = array('new_name' => $rts, 'old_name' => $r['searchname'], 'old_category' => $cats->getNameByID($r['categories_id']), 'new_category' => $cats->getNameByID($catId), 'group' => $groups->getNameByID($r['groups_id']), 'release_id' => $r['releases_id'], 'method' => 'File Name Match');
  $fixer->echoChangedReleaseName($echodata);

  if ($resp && $res && $catup) {
    echo $pdo->log->info("Done!");
  } else {
    var_dump($res);
  }
  $count++;
}

$time = $consoletools->convertTime(TIME() - $timestart);
echo $pdo->log->primary("Updated " . $count . " releases in ". $time .".");