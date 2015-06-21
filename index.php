<?php
    $path = "./admin/";
    require_once($path."autoloader.php");
    require_once($path."functions.php");
    require_once($path."db/SQLiteManager.php");

    $allowIds = true;
    
    $id = $_REQUEST["id"];
    $hash = $_REQUEST["hash"];
    if(!$id && !$hash) {
        $path = "./admin/";
	require_once($path."smarty/Smarty.class.php");
	require_once($path."db/SQLiteManager.php");

	$db = SQLiteManager\SQLiteManager::getInstance();

	$result = $db->select("feeds");
	$feeds = $db->fetchArray($result);

	//DISPLAY
	$smarty = new Smarty();
	$smarty->auto_literal = true;
	$data = new Smarty_Data();
	$data->assign("feeds", $feeds);
	$data->assign("base_url", "//".$_SERVER[HTTP_HOST].substr($_SERVER[REQUEST_URI],0,strrpos($_SERVER[REQUEST_URI],"/")));
	$smarty->addTemplateDir('admin/templates');
	$smarty->display("index.tpl", $data);
	return;
	//TODO add default page with public feed list here
        //if($allowIds){
        //    die("no id or hash");
        //}else{
        //    die("no hash");
        //}
    }
    $db = SQLiteManager\SQLiteManager::getInstance();
    $feedInfo = null;
    
    if($hash) {
        $result = $db->select("feeds", null, ["hash"=>$hash]);
        $feedInfo = $db->fetchArray($result)[0];
        if(!$feedInfo) {
            http_response_code(404);
            die("bad feed hash ".$hash);
        }
    }else if($id && !$hash && $allowIds) {
        $result = $db->select("feeds", null, ["ID"=>$id]);
        $feedInfo = $db->fetchArray($result)[0];
        if(!$feedInfo) {
            http_response_code(404);
            die("bad id ".$id);
        }
    }else if($id && !$hash && !$allowIds) {
        if(!$feedInfo) {
            http_response_code(404);
            die("requests via ids are not allowed");
        }
    }else{
    }
    

    $result = $db->select("filters", null, ["feedID"=>$feedInfo["ID"]]);
    $feedInfo["patterns"] = $db->fetchArray($result);

    $feed = new SimplePie();
    $feed->enable_cache(false);
    $feed->set_feed_url($feedInfo["feed"]);
    $feed->init();
    // This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
    $feed->handle_content_type();
?>
<?php 
header('Content-type: application/atom+xml');
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<feed xmlns=\"http://www.w3.org/2005/Atom\">";
?>
    <title><?= $feed->get_title(); ?></title>
    <?php 
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $protocol = 'https';
    }
    ?>
    <link href="<?= $protocol.'://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI] ?>" rel="self"/>
    <link href="<?= $feed->get_base(); ?>" />
    <id><?= $feed->get_permalink(); ?></id>
    <?php if($feed->get_authors()) { ?>
        <?php foreach($feed->get_authors() as $author) { ?>
            <author>
                <?php if($author->get_name()) { ?>
                    <name><?= $author->get_name(); ?></name>
                <?php } ?>
                <?php if($author->get_email()) { ?>
                    <email><?= $author->get_email(); ?></email>
                <?php } ?>
                <?php if($author->get_link()) { ?>
                    <uri><?= $author->get_link(); ?></uri>
                <?php } ?>
            </author>
        <?php } ?>
    <?php } ?>
    <?php if($feed->get_contributors()) { ?>
        <?php foreach($feed->get_contributors() as $acontributor) { ?>
            <contributor>
                <?php if($contributor->get_name()) { ?>
                    <name><?= $contributor->get_name(); ?></name>
                <?php } ?>
                <?php if($contributor->get_email()) { ?>
                    <email><?= $contributor->get_email(); ?></email>
                <?php } ?>
                <?php if($contributor->get_link()) { ?>
                    <uri><?= $contributor->get_link(); ?></uri>
                <?php } ?>
            </contributor>
        <?php } ?>
    <?php } ?>
    <?php if($feed->get_categories()) { ?>
        <?php foreach($feed->get_categories() as $category) { ?>
            <category term="<?= $category; ?>"/>
        <?php } ?>
    <?php } ?>
    <?php if($feed->get_copyright()) { ?>
        <rights><?= $feed->get_copyright(); ?></rights>
    <?php } ?>
    <?php if($feed->get_image_url()) { ?>
        <logo><?= $feed->get_image_url(); ?></logo>
    <?php } ?>
    <updated><?= date("c"); ?></updated> <?php /* WARNING: simplepie doesn't provide ANY feed-level date */ ?>
    <?php $i = 0; ?>
    <?php foreach($feed->get_items() as $item) { ?>
        <?php
            if(!filter($item, $feedInfo)) {
                continue;
            }
        ?>
        <entry>
            <title><?= $item->get_title(); ?></title>
            <link href="<?= $item->get_permalink(); ?>"/>
            <id><?= $item->get_id(); ?></id>
            <?php if($item->get_date()) { ?>
                <published><?= $item->get_date("c"); ?></published>
            <?php } ?>
            <?php if($item->get_updated_date()) { ?>
                <updated><?= $item->get_updated_date("c"); ?></updated>
            <?php } ?>
            <summary type="html"><![CDATA[<?= $item->get_description(); ?>]]></summary>
            <?php if($item->get_content(true)) { ?>
                <content type="html"><![CDATA[<?= $item->get_content(true); ?>]]></content>
            <?php } ?>
            <?php if($item->get_authors()) { ?>
                <?php foreach($item->get_authors() as $author) { ?>
                    <author>
                        <?php if($author->get_name()) { ?>
                            <name><?= $author->get_name(); ?></name>
                        <?php } ?>
                        <?php if($author->get_email()) { ?>
                            <email><?= $author->get_email(); ?></email>
                        <?php } ?>
                        <?php if($author->get_link()) { ?>
                            <uri><?= $author->get_link(); ?></uri>
                        <?php } ?>
                    </author>
                <?php } ?>
            <?php } ?>
            <?php if($item->get_contributors()) { ?>
                <?php foreach($item->get_contributors() as $acontributor) { ?>
                    <contributor>
                        <?php if($contributor->get_name()) { ?>
                            <name><?= $contributor->get_name(); ?></name>
                        <?php } ?>
                        <?php if($contributor->get_email()) { ?>
                            <email><?= $contributor->get_email(); ?></email>
                        <?php } ?>
                        <?php if($contributor->get_link()) { ?>
                            <uri><?= $contributor->get_link(); ?></uri>
                        <?php } ?>
                    </contributor>
                <?php } ?>
            <?php } ?>
            <?php if($item->get_categories()) { ?>
                <?php foreach($item->get_categories() as $category) { ?>
                    <category<?php if($category->get_scheme()) { ?> scheme="<?= $category->get_scheme(); ?>"<?php } ?><?php if($category->get_term()) { ?> term="<?= $category->get_term(); ?>"<?php } ?><?php if($category->get_label()) { ?> label="<?= $category->get_label(); ?>"<?php } ?>/>
                <?php } ?>
            <?php } ?>
            <?php if($item->get_copyright()) { ?>
                <rights><?= $item->get_copyright(); ?></rights>
            <?php } ?>
        </entry>
        <?php if(++$i == $feedInfo["maxItems"]) { break; } ?>
    <?php } ?>
</feed>