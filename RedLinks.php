<?php

$wgExtensionCredits['variable'][] = array(
    'path'           => __FILE__,
	'name'			=> 'RedLinks',
	'author'		=> 'Joe ST',
	'version'		=> '0.2',
	'description'	=> 'Counts the number of wanted articles',
	'url'			=> 'https://github.com/FallingBullets/mw-redlinks'
);

$wgExtensionMessagesFiles['RedLinksMagic'] = dirname(__FILE__) . '/RedLinks.i18n.magic.php';
 
$wgHooks['ParserGetVariableValueSwitch'][] = 'CountWantedPages';
function CountWantedPages( &$parser, &$cache, &$magicWordId, &$ret ) {
	if ( "__RLK" != $magicWordId )
		return false;
	$dbr = wfGetDB( DB_SLAVE );

	/*
SELECT pl_namespace AS namespace, COUNT( * ) AS count
FROM mw_pagelinks
LEFT JOIN mw_page ON (pl_namespace = page_namespace AND pl_title = page_title)
WHERE page_namespace IS NULL 
GROUP BY pl_namespace
	*/	
	$res = $dbr->select(
		array('pagelinks', 'page'),
		array( 'namespace' => 'pl_namespace', 'count'=>'COUNT(*)' ),
		'page_namespace IS NULL',
		__METHOD__,
		array( 'GROUP BY' => 'pl_namespace' ),
		array(
			'page' => array(
				'LEFT JOIN', array(
					'page_namespace = pl_namespace',
					'page_title = pl_title'
				)
			)
		)
	);

	foreach( $res as $row )
		if ($row->namespace == 0)
			$ret = $row->count;

	return true;
}
 
$wgHooks['MagicWordwgVariableIDs'][] = 'wfMyDeclareVarIds';
function wfMyDeclareVarIds( &$customVariableIds ) {
        // $customVariableIds is where MediaWiki wants to store its list of custom
        // variable IDs. We oblige by adding ours:
        $customVariableIds[] = '__RLK';
 
        // must do this or you will silence every MagicWordwgVariableIds hook
        // registered after this!
        return true;
}
