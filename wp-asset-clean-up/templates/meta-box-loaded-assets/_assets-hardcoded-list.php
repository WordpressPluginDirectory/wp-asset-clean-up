<?php
if (! isset($data)) {
	exit; // no direct access
}

$totalFoundHardcodedTags = $totalHardcodedTags = 0;
$hardcodedTags = $data['all']['hardcoded'];

$contentWithinConditionalComments = \WpAssetCleanUp\ObjectCache::wpacu_cache_get('wpacu_hardcoded_content_within_conditional_comments');

$totalFoundHardcodedTags  = isset($hardcodedTags['link_and_style_tags']) ? count($hardcodedTags['link_and_style_tags']) : 0;
$totalFoundHardcodedTags += isset($hardcodedTags['script_src_or_inline_and_noscript_inline_tags'])
                            ? count($hardcodedTags['script_src_or_inline_and_noscript_inline_tags']) : 0;

if ($totalFoundHardcodedTags === 0) {
	return; // Don't print anything if there are no hardcoded tags available
}
?>
<?php if (isset($data['print_outer_html']) && $data['print_outer_html']) { ?>
<div class="wpacu-assets-collapsible-wrap wpacu-wrap-area wpacu-hardcoded">
    <a class="wpacu-assets-collapsible wpacu-assets-collapsible-active" href="#" style="padding: 15px 15px 15px 44px;">
        <span class="dashicons dashicons-code-standards"></span> Hardcoded (non-enqueued) Styles &amp; Scripts &#10141; Total: <?php echo $totalFoundHardcodedTags; ?>
    </a>
    <div class="wpacu-assets-collapsible-content" style="max-height: inherit;">
<?php } ?>
        <div style="padding: 0;">
            <div style="margin: 15px 0 0;">
                <p><span style="color: #0073aa;" class="dashicons dashicons-info"></span> The following tags are NOT LOADED via the recommended <a target="_blank"
                                                                     href="https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/">wp_enqueue_scripts()</a>
                action hook (despite the name, it is used for enqueuing both scripts and styles) which is the proper one to use when enqueuing scripts and styles that are meant to appear on
                the front end. The standard functions that are used inside the hook to do an enqueuing are: <a target="_blank"
                                                                                                               href="https://developer.wordpress.org/reference/functions/wp_enqueue_style/">wp_enqueue_style()</a>,
	            <a target="_blank" href="https://codex.wordpress.org/Function_Reference/wp_add_inline_style">wp_add_inline_style()</a>,
	            <a target="_blank" href="https://developer.wordpress.org/reference/functions/wp_enqueue_script/">wp_enqueue_script()</a>
	            &amp; <a target="_blank"
	                     href="https://developer.wordpress.org/reference/functions/wp_add_inline_script/">wp_add_inline_script()</a>. The tags could have been added via editing the PHP code (not using the right standard functions), directly inside posts content, widgets or via plugins such as "Insert Headers and Footers", "Head, Footer and Post Injections", etc. Be careful when unloading any of these tags as they might be related to Google Analytics/Google Ads, StatCounter, Facebook Pixel, etc.
                </p>
                <!-- [wpacu_lite] -->
                <div style="margin: 20px 0; border-left: solid 4px green; background: #f2faf2; padding: 10px;"><img width="20" height="20" src="<?php echo esc_url(WPACU_PLUGIN_URL); ?>/assets/icons/icon-lock.svg" valign="top" alt="" /> &nbsp;Managing hardcoded LINK/STYLE/SCRIPT tags is an option <a target="_blank" href="<?php echo apply_filters('wpacu_go_pro_affiliate_link', WPACU_PLUGIN_GO_PRO_URL.'?utm_source=manage_hardcoded_assets&utm_medium=top_notice'); ?>">available in the Pro version</a>.</div>
                <!-- [/wpacu_lite] -->
            </div>
			<?php
            $handlesInfo = \WpAssetCleanUp\Main::getHandlesInfo();

			foreach (array('link_and_style_tags', 'script_src_or_inline_and_noscript_inline_tags') as $targetKey) {
                if ( ! (isset($hardcodedTags[$targetKey]) && ! empty($hardcodedTags[$targetKey])) ) {
                    continue; // None found in the list? do not trigger the code below!
                }

				$hardcodedTags[ $targetKey ] = array_unique( $hardcodedTags[ $targetKey ] );

				if ( ! empty( $hardcodedTags[ $targetKey ] ) ) {
					$totalTagsForTarget  = count( $hardcodedTags[ $targetKey ] );
					?>
					<div>
						<div class="wpacu-content-title wpacu-has-toggle-all-assets">
							<h3>
								<?php if ($targetKey === 'link_and_style_tags') { ?>Hardcoded LINK (stylesheet) &amp; STYLE tags<?php } ?>
								<?php if ($targetKey === 'script_src_or_inline_and_noscript_inline_tags') { ?>Hardcoded SCRIPT (with "src" attribute &amp; inline) and NOSCRIPT inline tags<?php } ?>
							</h3>

                            <div class="wpacu-area-toggle-all-assets wpacu-right">
                                <a class="wpacu-area-contract-all-assets wpacu_area_handles_row_expand_contract"
                                   data-wpacu-area="hardcoded_<?php echo $targetKey; ?>" href="#">Contract</a>
                                |
                                <a class="wpacu-area-expand-all-assets wpacu_area_handles_row_expand_contract"
                                   data-wpacu-area="hardcoded_<?php echo $targetKey; ?>" href="#">Expand</a>
                                All Assets
                            </div>
						</div>
						<table class="wpacu_list_table wpacu_striped" data-wpacu-area="hardcoded_<?php echo $targetKey; ?>">
							<tbody>
							<?php
							$hardcodedTagsOutput = '';

							foreach ( $hardcodedTags[ $targetKey ] as $indexNo => $tagOutput ) {
								$contentUniqueStr = \WpAssetCleanUp\HardcodedAssets::determineHardcodedAssetSha1($tagOutput);

								/*
								 * 1) Hardcoded LINK (stylesheet) &amp; STYLE tags
								*/
								if ($targetKey === 'link_and_style_tags') {
									// For LINK ("stylesheet")
									if ( stripos( $tagOutput, '<link ' ) === 0 ) {
										$generatedHandle  = 'wpacu_hardcoded_link_' . $contentUniqueStr;

                                        // could be href="value_here" or href  = "value_here" (with extra spaces) / make sure it matches
										if ( preg_match('#href(\s+|)=(\s+|)#Umi', $tagOutput) ) {
										    $linkHrefOriginal = \WpAssetCleanUp\Misc::getValueFromTag($tagOutput);
                                        }

										// No room for any mistakes, do not print the cached files
										if (strpos($linkHrefOriginal, \WpAssetCleanUp\OptimiseAssets\OptimizeCommon::getRelPathPluginCacheDir()) !== false) {
										    continue;
										}

										$dataRowObj = (object) array(
											'handle'        => $generatedHandle,
											'src'           => $linkHrefOriginal,
											'tag_output'    => $tagOutput
										);

										$dataRowObj->inside_conditional_comment = \WpAssetCleanUp\HardcodedAssets::isWithinConditionalComment($tagOutput, $contentWithinConditionalComments);

										// Determine source href (starting with '/' but not starting with '//')
										if (strpos($linkHrefOriginal, '/') === 0 && strpos($linkHrefOriginal, '//') !== 0) {
											$dataRowObj->srcHref = get_site_url() . $linkHrefOriginal;
										} else {
											$dataRowObj->srcHref = $linkHrefOriginal;
										}

										$dataHH = $data;
										$dataHH['row']               = array();
                                        $dataHH['row']['asset_type'] = 'styles';
										$dataHH['row']['obj']        = $dataRowObj;

										$templateRowOutput = \WpAssetCleanUp\Main::instance()->parseTemplate(
											'/meta-box-loaded-assets/_hardcoded/_asset-style-single-row-hardcoded',
											$dataHH
										);

										$hardcodedTagsOutput .= $templateRowOutput;
									}

									// For STYLE (inline)
									if ( stripos( $tagOutput, '<style' ) === 0 ) {
										$generatedHandle  = 'wpacu_hardcoded_style_' . $contentUniqueStr;

										$dataRowObj = (object) array(
											'handle'        => $generatedHandle,
											'src'           => false,
											'tag_output'    => $tagOutput
										);

										$dataRowObj->inside_conditional_comment = \WpAssetCleanUp\HardcodedAssets::isWithinConditionalComment($tagOutput, $contentWithinConditionalComments);

										$dataHH = $data;
										$dataHH['row']               = array();
										$dataHH['row']['asset_type'] = 'styles';
										$dataHH['row']['obj']        = $dataRowObj;

										$templateRowOutput = \WpAssetCleanUp\Main::instance()->parseTemplate(
											'/meta-box-loaded-assets/_hardcoded/_asset-style-single-row-hardcoded',
											$dataHH
										);

										$hardcodedTagsOutput .= $templateRowOutput;
									}

									$totalHardcodedTags++;
								} elseif ($targetKey === 'script_src_or_inline_and_noscript_inline_tags') {
								/*
								 * 2) Hardcoded SCRIPT (with "src" attribute & inline) or Hardcoded NOSCRIPT inline tags
								*/
									$generatedHandle = $srcHrefOriginal = false;

									if ( stripos( $tagOutput, '<script' ) === 0 ) {
										if ( preg_match( '#src(\s+|)=(\s+|)#Umi', $tagOutput ) ) {
											$srcHrefOriginal = \WpAssetCleanUp\Misc::getValueFromTag( $tagOutput );
										}

										if ( $srcHrefOriginal ) {
											// No room for any mistakes, do not print the cached files
											if ( strpos( $srcHrefOriginal,
													\WpAssetCleanUp\OptimiseAssets\OptimizeCommon::getRelPathPluginCacheDir() ) !== false ) {
												continue;
											}
											$handlePrefix    = 'wpacu_hardcoded_script_src_';
											$generatedHandle = $handlePrefix . $contentUniqueStr;
										}

										// Is it a SCRIPT without "src" attribute? Then it's an inline one
										if ( ! $generatedHandle ) {
											$handlePrefix    = 'wpacu_hardcoded_script_inline_';
											$generatedHandle = $handlePrefix . $contentUniqueStr;
										}
									} elseif ( stripos( $tagOutput, '<noscript' ) === 0 ) {
										$handlePrefix    = 'wpacu_hardcoded_noscript_inline_';
										$generatedHandle = $handlePrefix . $contentUniqueStr;
                                    }

									$dataRowObj = (object)array(
										'handle'        => $generatedHandle,
										'tag_output'    => $tagOutput
									);

									if ($srcHrefOriginal) {
										$dataRowObj->src = $srcHrefOriginal;
									}

									$dataRowObj->inside_conditional_comment = \WpAssetCleanUp\HardcodedAssets::isWithinConditionalComment($tagOutput, $contentWithinConditionalComments);

									// Determine source href (starting with '/' but not starting with '//')
                                    if ($srcHrefOriginal) {
	                                    if ( strpos( $srcHrefOriginal, '/' ) === 0 && strpos( $srcHrefOriginal, '//' ) !== 0 ) {
		                                    $dataRowObj->srcHref = get_site_url() . $srcHrefOriginal;
	                                    } else {
		                                    $dataRowObj->srcHref = $srcHrefOriginal;
	                                    }
                                    }

									$dataHH = $data;
									$dataHH['row']               = array();
									$dataHH['row']['asset_type'] = 'scripts';
									$dataHH['row']['obj']        = $dataRowObj;

                                    $templateRowOutput = \WpAssetCleanUp\Main::instance()->parseTemplate(
										'/meta-box-loaded-assets/_hardcoded/_asset-script-single-row-hardcoded',
										$dataHH
									);

									$totalHardcodedTags++;

									$hardcodedTagsOutput .= $templateRowOutput;
								}
							}

							echo \WpAssetCleanUp\Misc::stripIrrelevantHtmlTags($hardcodedTagsOutput);
							?>
							</tbody>
						</table>
					</div>
					<?php
				}
			}
			?>
        </div>
<?php if (isset($data['print_outer_html']) && $data['print_outer_html']) { ?>
    </div>
</div>
<?php }
