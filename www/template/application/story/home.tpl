<span metal:define-macro="home" tal:omit-tag="" >

                        <table width="100%">
							<tr><td align="left">
                                    <div align="left">Hier kann der vollständige rote Faden eingesehen und bearbeitet werden.<br />
                                      <br />
                                    </div>
                            </td></tr>
                        </table>

                        <center>
						<table width="90%" border="0">
							<!--<tr><td tal:content="php:var_dump(story_info)"></td></tr>-->
							<span tal:repeat="day story_info/days">
								<tr><td style="font-weight:900;">Tag <span tal:content="php: (day['day_offset'] + 1)"></span></td></tr>
								<tr><td>
									<textarea name="story" tal:attributes="id day/id" class="story_day" style="width:100%" tal:content="day/story"></textarea>
								</td></tr>
							</span>
						</table>
						</center>
</span>