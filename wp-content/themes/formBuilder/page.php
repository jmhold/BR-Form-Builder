<?php


get_header(); ?>


		<!-- Header -->
            <div id="header">
                
            </div><!-- End Header -->

            
              <div id="content" >
                    
                    <div class="row-fluid">
                    
                    		<!-- Widget -->
                            <div class="widget span12 clearfix">
                            
                                <div class="widget-header">
                                    <span><i class="icon-align-left"></i>  Ziceadmin Form </span>
                                </div><!-- End widget-header -->	
                                
                                <div class="widget-content">
                                    <!-- title box -->
                                    <div class="boxtitle"><i class="icon-hdd"></i> Basic Form ,Elements and custom input , go to advace <span class="netip"><a href="vform.html" class="red" title="Live demo  Validation"> form Validation and  Live demo  </a></span>
                                      </div>
                                      <form id="demo"> 
                                            <div class="section" >
                                                <label> full <small>Text custom</small></label>   
                                                <div> <input type="text" name="text[]" class=" full"  /><span class="f_help">Text custom help</span></div>
                                           </div>
                                            <div class="section">
                                                <label> large <small>Text custom</small></label>   
                                                <div> <input type="text"  class=" large" /><span class="f_help">Text custom help</span></div>
                                           </div>
                                            <div class="section">
                                                <label> medium <small>Text custom</small></label>   
                                                <div> <input type="text"  class=" medium" /><span class="f_help">Text custom help</span></div>
                                                <div> <input type="text"  class=" medium" /><span class="f_help">Text custom help</span></div>
                                           </div>
                                            <div class="section">
                                                <label> small <small>Text custom</small></label>   
                                                <div> <input type="text"  class=" small" /></div>
                                           </div>
                                            <div class="section">
                                                <label> xsmall <small>Text custom</small></label>   
                                                <div> <input type="text"  class=" xsmall" /></div>
                                           </div>
                                            <div class="section">
                                                <label> xxsmall <small>Text custom</small></label>   
                                                <div> <input type="text" class=" xxsmall" /></div>
                                           </div>
                                            <div class="section">
                                                <label>Iphone checkbox<small>Text custom</small></label>
                                                <div>
                                                         <input type="checkbox"  class="on_off_checkbox"  value="1"  checked="checked"  />
                                               </div>
                                                <div>
                                                         <input type="checkbox"  class="on_off_checkbox"  value="1"   />
                                                        <span class="f_help">ON / OFF  </span> 
                                               </div>
                                                <div>
                                                         <input type="checkbox" class="on_off_checkbox"  disabled="disabled"  />
                                                        <span class="f_help">Disabled</span> 
                                               </div>
                                           </div>
                                            <div class="section">
                                                <label> checkbox custom<small>Text custom</small></label>   
                                                <div> 
                                                          <input type="checkbox" id="preOrder" name="preOrder"   class="preOrder" value="1"   />
                                                          <span class="f_help">Ex. checkbox custom  </span> 
                                               </div>
                                               </div>
                                            <div class="section">
                                                <label> checkbox css3<small>Text custom</small></label>   
                                               <div>
                                                     <div class="checksquared">
                                                            <input type="checkbox" name="genre" id="checkNormal"  value="comedy"/>
                                                            <label for="checkNormal" title="Normal"></label>
                                                     </div>   
                                                     <div class="checksquared">     
                                                            <input type="checkbox" name="genre" id="checked" value="comedy"  checked="checked" />
                                                            <label  for="checked" title="Checked"></label>
                                                     </div>
                                                     <div class="checksquared">
                                                            <input type="checkbox" name="genre" id="checkDisabled"  value="action"  disabled="disabled"/>
                                                            <label  for="checkDisabled" title="Disabled"> </label>
                                                    </div>
                                                     <div class="checksquared">
                                                            <input type="checkbox" name="genre" id="checkedDisabled"  value="action"  disabled="disabled" checked="checked"/>
                                                            <label  for="checkedDisabled" title="Checked Disabled "></label>
                                                     </div>
                                               </div>
                                            </div>
                                            <div class="section">
                                                <label>Radio css3 <small>Text custom</small></label>   
                                               <div>
                                                    <div class="radiorounded">
                                                        <input type="radio" name="opinions" id="radio-1" value="1" />
                                                        <label for="radio-1" title="Totally"></label>
                                                    </div>
                                                    <div class="radiorounded">
                                                        <input type="radio" name="opinions" id="radio-2" value="1" checked="checked"/>
                                                        <label for="radio-2"  title="You must be kidding"></label>
                                                    </div>
                                                    <div class="radiorounded">
                                                        <input type="radio" name="opinions" id="radio-3" value="1" disabled="disabled"/>
                                                        <label for="radio-3" title="Radio Disabled" ></label>
                                                    </div>
                                                </div>
                                            </div>
              
                                              
                                            <div class="section">
                                                <label> checkbox  css3 Limit<small>Max 3 Selected</small></label>   
                                               <div class="limit3m">
                                                  <div class="checksquared">
                                                      <input type="checkbox" name="genre" id="check-1" value="action"   checked="checked"/>
                                                      <label  for="check-1" title="Action / Adventure"></label>
                                                  </div>
                                                  <div class="checksquared">  
                                                      <input type="checkbox" name="genre" id="check-2" value="comedy" checked="checked"/>
                                                      <label for="check-2" title="Comedy"></label>
                                                    </div>
                                                    <div class="checksquared">  
                                                      <input type="checkbox" name="genre" id="check-3" value="epic" />
                                                      <label  for="check-3" title="Epic / Historical"></label>
                                                     </div>
                                                     <div class="checksquared">
                                                      <input type="checkbox" name="genre" id="check-4" value="science"  />
                                                      <label for="check-4" title="Science Fiction"></label>
                                                      </div>
                                                      <div class="checksquared">
                                                      <input type="checkbox" name="genre" id="check-5" value="romance" />
                                                      <label for="check-5" title="Romance"></label>
                                                      </div>
                                                  <span class="f_help"><span>**</span> Your  Can Selected Max 3 item</span> 
                                               </div>
                                           </div>
                                            <div class="section">
                                              <label> rating star </label> 
                                                  <div><input name="my_input" value="5" id="rating_star" type="hidden"></div>
                                                  <div><input name="my_input" value="7" id="rating_star2" type="hidden"></div>
                                            </div>
              
                                          <div class="section ">
                                          <label> Avartar <small>Fileupload</small></label>   
                                          <div> 
                                              <input type="file" class="fileupload" />
                                          </div>
                                          </div>
                                          <div class="section">
                                            <label>select normal <small>Fix width</small></label>   
                                            <div>
                                                <select class="small">
                                                   <option value="1"  >One</option>
                                                   <option value="2"  >Two</option>
                                                   <option value="3"  >Three</option>
                                                   <option value="4"  >Four</option>
                                                   <option value="5"  >five</option>
                                              </select>       
                                      </div>
                                      </div>
                                          <div class="section">
                                            <label>select With search<small>Fix width</small></label>   
                                            <div>
                                                <select data-placeholder="Choose a Country..." class="chzn-select" tabindex="2" >
                                                <option value=""></option> 
                                                <option value="United States">United States</option> 
                                                <option value="United Kingdom">United Kingdom</option> 
                                                <option value="Afghanistan">Afghanistan</option> 
                                                <option value="Albania">Albania</option> 
                                                <option value="Algeria">Algeria</option> 
                                                <option value="Austria">Austria</option> 
                                                <option value="Azerbaijan">Azerbaijan</option> 
                                                <option value="Bahamas">Bahamas</option> 
                                                <option value="Bahrain">Bahrain</option> 
                                                <option value="Bangladesh">Bangladesh</option> 
                                                <option value="Barbados">Barbados</option> 
                                                <option value="Belarus">Belarus</option> 
                                                <option value="Belgium">Belgium</option> 
                                                <option value="Belize">Belize</option> 
                                                <option value="Benin">Benin</option> 
                                                <option value="Bermuda">Bermuda</option> 
                                                <option value="Poland">Poland</option> 
                                                <option value="Portugal">Portugal</option> 
                                                <option value="Puerto Rico">Puerto Rico</option> 
                                                <option value="Qatar">Qatar</option> 
                                                <option value="Reunion">Reunion</option> 
                                                <option value="Romania">Romania</option> 
                                                <option value="Samoa">Samoa</option> 
                                                <option value="San Marino">San Marino</option> 
                                                <option value="Sao Tome and Principe">Sao Tome and Principe</option> 
                                                <option value="Saudi Arabia">Saudi Arabia</option>
                                                <option value="Switzerland">Switzerland</option> 
                                                <option value="Syrian Arab Republic">Syrian Arab Republic</option> 
                                                <option value="Taiwan, Republic of China">Taiwan, Republic of China</option> 
                                                <option value="Tajikistan">Tajikistan</option> 
                                                <option value="Tanzania, United Republic of">Tanzania, United Republic of</option> 
                                                <option value="Thailand">Thailand</option> 
                                                <option value="United Arab Emirates">United Arab Emirates</option> 
                                                <option value="Zambia">Zambia</option> 
                                                <option value="Zimbabwe">Zimbabwe</option>
                                              </select>       
                                      </div>
                                      </div>
                                      
                                            <div class="section">
                                            <label> Mullti select <small>Text custom</small></label>   
                                            <div> 
                                              <select  class="chzn-select " multiple tabindex="4">
                                                <option value=""></option> 
                                                <option value="United States" >United States</option> 
                                                <option value="United Kingdom">United Kingdom</option> 
                                                <option value="Afghanistan">Afghanistan</option> 
                                                <option value="Albania">Albania</option> 
                                                <option value="Algeria">Algeria</option> 
                                                <option value="American Samoa">American Samoa</option> 
                                                <option value="Andorra">Andorra</option> 
                                                <option value="Angola" selected="selected">Angola</option> 
                                                <option value="Anguilla" selected="selected">Anguilla</option> 
                                                <option value="Antarctica">Antarctica</option> 
                                                <option value="Antigua and Barbuda">Antigua and Barbuda</option> 
                                                <option value="Argentina">Argentina</option> 
                                                <option value="Armenia">Armenia</option> 
                                                <option value="Aruba">Aruba</option> 
                                              </select></div>
                                           </div>
                                             <div class="section">
                                                 <label> Tag input  <small><img src="images/icon/new.gif"></small></label>   
                                               <div ><input id="tags_input" type="text" class="tags" value="webstie,manager,webdesign,roffle" /></div>   
                                   			</div>
                                             <div class="section">
                                                 <label> textarea  <small>Elastic</small></label>   
                                                <div > <textarea name="Textareaelastic" id="Textareaelastic"  class="large"  cols="" rows=""></textarea></div>   
                                                
                                             </div>
                                 <div class="section">
                                                 <label> textarea  <small>Limit chars</small></label>   
                                                <div>
                                                <textarea name="Textarealimit" id="Textarealimit"  class="large"  cols="" rows=""></textarea>
                                                <span class="f_help"> Limit chars <span class="limitchars">140</span></span>
                                                </div>   
                                                
                                             </div>
                                            <div class="section last">
                                                <div><a class="uibutton loading" title="Saving" rel="1" >submit</a> <a class="uibutton special"  >clear form</a> <a class="uibutton loading confirm" title="Checking" rel="0" >Check</a> </div>
                                           </div>
                                        </form>
                                </div><!--  end widget-content -->
                            </div><!-- widget  span12 clearfix-->

                    </div><!-- row-fluid -->
                    
              </div> <!--// End ID content -->

		
              <div id="content" >
								<?php if ( have_posts() ) : ?>

									<?php /* Start the Loop */ ?>
									<?php while ( have_posts() ) : the_post(); ?>
										<?php the_content(); ?>
									<?php endwhile; ?>

								<?php else : ?>
									
								<?php endif; // end have_posts() check ?>
							</div>

<?php get_footer(); ?>