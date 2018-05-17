<span metal:define-macro="welcome" tal:omit-tag="">
	<center>Tschau du Bastard &#x2764;</center>
	<br />
	<img src="https://www.pfadiangenstein.ch/images/angenstein.png" alt="Pfadi Angenstein" style="margin-left: calc(50% - 60px);" width="120"></img>
	
    <tal:block condition="inventions">
        <br />
        <b>Du hast <span tal:content="num_inventions" tal:omit-tag=""></span> neue Lagereinladungen. <a href="index.php?app=camp_admin">Einladungen ansehen...</a></b>
        <br />
        <br />
    </tal:block>
</span>
