<?php
/**
 * @var \Palasthotel\WordPress\LiveNews\Render $this
 * @var \Palasthotel\WordPress\LiveNews\Model\Particle $particle
 */
?>
<li
	data-particle-created="<?php echo $particle->created->format( "U" ); ?>"
	data-particle-modified="<?php echo $particle->modified->format( "U" ); ?>"
	data-particle-id="<?php echo $particle->id; ?>"
><?php
	$author = get_userdata($particle->author_id);
	$authorName = "Unknown";
	if($author){
		$authorName = $author->display_name;
	}
	$date = $particle->created->format("Y-m-d H:i:s");
	echo "<div>$date</div>";
	echo "<div>$authorName</div>";
	echo "<div>";

	foreach ($particle->getContents() as $content){
		$this->renderParticleContent($content);
	}
	echo "</div>";
?></li>
