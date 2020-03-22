<span metal:define-macro="home" tal:omit-tag="" >
                        <table width="100%">
							<tr><td align="left">
                                    <div align="left">Hier werden einige Analysedaten des Lagers aufbereitet.<br />
                                      <br />
                                      <br />
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
						<br />
						<br />
						<br />
						<br />




						<br />
						<br />
						<div class="chart_box_wrapper">
							<div class="chart_box">
								<b>Auswertung Blockinhalte</b>
								<br />
								<br />
								<div class="chart_wrapper">
									<div id="chart_blocktypes_empty" class="hidden">Kein Block wurde einem Typ zugewiesen. Bitte nachholen!</div>
									<div id="chart_blocktypes_breadcrumbs" class="chart_breadcrumbs"></div>
									<div class="chart_container">
										<canvas id="chart_blocktypes">Kuchendiagramm kann nicht angezeigt werden. Bitte anderen Browser verwenden.</canvas>
									</div>
									<div class="chart_eventlist_wrapper">
										<b>Blöcke:</b><br />
										Typ: <span id="chart_blocktypes_eventlist_type">-</span>
										<div><ul id="chart_blocktypes_eventlist"></ul></div>
									</div>
								</div>
							</div>
							<div class="chart_box">
								<b>Kategorisierte Blöcke</b>
								<br />
								<br />
								<div class="chart_wrapper">
									<div id="chart_emptytypes_breadcrumbs" class="chart_breadcrumbs"></div>
									<div class="chart_container">
										<canvas id="chart_emptytype">Kuchendiagramm kann nicht angezeigt werden. Bitte anderen Browser verwenden.</canvas>
									</div>
									<div class="chart_eventlist_wrapper">
										<b>Blöcke:</b><br />
										Typ: <span id="chart_emptytypes_eventlist_type">-</span>
										<div><ul id="chart_emptytypes_eventlist"></ul></div>
									</div>
								</div>
							</div>
						</div>
</span>