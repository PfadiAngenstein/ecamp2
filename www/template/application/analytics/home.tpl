<span metal:define-macro="home" tal:omit-tag="" >
                        <table width="100%">
							<tr><td align="left">
                                    <div align="left">Hier werden einige Analysedaten des Lagers aufbereitet.<br />
                                      <br />
                                    </div>
                            </td></tr>
                        </table>



						<b>J+S Konformität</b>
						<br />
						<br />
						Ganzes Lager: 
						<font tal:condition="jsAnalytics/allOk" color="green">
							J+S Konform
						</font>
						<font tal:condition="not: jsAnalytics/allOk" color="red">
							nicht J+S Konform
						</font>
						<br />
						<br />
						<div class="jsValidBox">
							<table width="100%" border="0">
								<tr>
									<tal:block repeat="day jsAnalytics/days">
										<td>
											<a tal:attributes="href day/link">
												<font tal:condition="day/dayOk" color="green">
													(<tal:block content="day/offset" />) <tal:block content="day/date" />
												</font>
												<font tal:condition="not: day/dayOk" color="red">
													(<tal:block content="day/offset" />) <tal:block content="day/date" />
												</font>
											</a>

											<br />

											Mind. 2h J+S Programm: <tal:block content="php: (day['fourHours']) ? 'Ja' : 'Nein'" />
											<br />
											Mind. 2 Tageszeiten: <tal:block content="php: (day['twoTimes']) ? 'Ja' : 'Nein'" />
											<br />
											<br />
											J+S Relevante Blöcke:
											<ul>
												<li tal:repeat="event day/events">
													<a  href="#" tal:attributes="onClick event/link">
														<tal:block content="event/name" /> (<tal:block content="event/length" /> min)
													</a>
												</li>
											</ul>
										</td>
									</tal:block>
								</tr>
							</table>
						</div>
</span>