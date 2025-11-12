<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication('com_splms');
$params = $app->getParams();
$assets_path = Uri::root(true) . '/components/com_splms/assets/';

$video = $displayData['video'];
$thumbnail = (isset($displayData['thumbnail']) && $displayData['thumbnail']) ? Uri::root(true) . '/' . $displayData['thumbnail'] : '';

$doc = Factory::getDocument();
$doc->addScript( Uri::root(true) . '/components/com_splms/assets/js/video-player/FWDEVPlayer.js' );
?>
<script type="text/javascript">
    FWDEVPUtils.onReady(function(){
    
        FWDEVPlayer.videoStartBehaviour = "pause";
        
        new FWDEVPlayer({		
            //main settings
            instanceName:"splmsVideoPlayer1",
            parentId:"splmsVideoPlayer",
            mainFolderPath:"<?php echo Uri::root(true) . '/components/com_splms/assets/images/video-player'; ?>",
            initializeOnlyWhenVisible:"no",
            skinPath:"minimal_skin_dark",
            displayType:"responsive",
            fillEntireVideoScreen:"no",
            playsinline:"yes",
            useWithoutVideoScreen:"no",
            autoScale:"yes",
            openDownloadLinkOnMobile:"no",
            useVectorIcons:"no",
            useResumeOnPlay:"no",
            goFullScreenOnButtonPlay:"no",
            useHEXColorsForSkin:"no",
            normalHEXButtonsColor:"#FF0000",
            privateVideoPassword:"428c841430ea18a70f7b06525d4b748a",
            startAtTime:"",
            stopAtTime:"",
            startAtVideoSource:1,
            videoSource:[{source:"<?php echo $video; ?>"}],
            posterPath:"<?php echo $thumbnail; ?>",
            showErrorInfo:"yes",
            fillEntireScreenWithPoster:"yes",
            disableDoubleClickFullscreen:"no",
            addKeyboardSupport:"yes",
            useChromeless:"no",
            showPreloader:"yes",
            preloaderColors:["#999999", "#FFFFFF"],
            autoPlay:"no",
            enableAutoplayOnMobile:"no",
            loop:"no",
            scrubAtTimeAtFirstPlay:"00:00:00",
            maxWidth:1170,
            maxHeight:659,
            volume:.8,
            greenScreenTolerance:200,
            backgroundColor:"#000000",
            posterBackgroundColor:"#000000",
            //lightbox settings
            closeLightBoxWhenPlayComplete:"no",
            lightBoxBackgroundOpacity:.6,
            lightBoxBackgroundColor:"#000000",
            //logo settings
            showLogo:"no",
            hideLogoWithController:"yes",
            logoPosition:"topRight",
            logoLink:"#",
            logoMargins:5,
            //controller settings
            showController:"yes",
            showDefaultControllerForVimeo:"no",
            showScrubberWhenControllerIsHidden:"yes",
            showControllerWhenVideoIsStopped:"yes",
            showVolumeScrubber:"yes",
            showVolumeButton:"yes",
            showTime:"yes",
            showRewindButton:"yes",
            showQualityButton:"yes",
            showShareButton:"no",
            showDownloadButton:"yes",
            showMainScrubberToolTipLabel:"yes",
            showChromecastButton:"no",
            showFullScreenButton:"yes",
            repeatBackground:"yes",
            controllerHeight:41,
            controllerHideDelay:3,
            startSpaceBetweenButtons:7,
            spaceBetweenButtons:9,
            mainScrubberOffestTop:14,
            scrubbersOffsetWidth:4,
            timeOffsetLeftWidth:5,
            timeOffsetRightWidth:3,
            volumeScrubberWidth:80,
            volumeScrubberOffsetRightWidth:0,
            timeColor:"#777777",
            youtubeQualityButtonNormalColor:"#777777",
            youtubeQualityButtonSelectedColor:"#FFFFFF",
            scrubbersToolTipLabelBackgroundColor:"#FFFFFF",
            scrubbersToolTipLabelFontColor:"#5a5a5a",
            //redirect at video end
            redirectURL:"",
            redirectTarget:"_blank",
            //cuepoints
            executeCuepointsOnlyOnce:"no",
            cuepoints:[],
            //audio visualizer
            audioVisualizerLinesColor:"#0099FF",
            audioVisualizerCircleColor:"#FFFFFF",
            //advertisement on pause window
            aopwTitle:"Advertisement",
            aopwSource:"",
            aopwWidth:400,
            aopwHeight:240,
            aopwBorderSize:6,
            aopwTitleColor:"#FFFFFF",
            //playback rate / speed
            showPlaybackRateButton:"yes",
            defaultPlaybackRate:"1", //0.25, 0.5, 1, 1.25, 1.5, 2
            //sticky on scroll
            stickyOnScroll:"no",
            stickyOnScrollShowOpener:"yes",
            stickyOnScrollWidth:"700",
            stickyOnScrollHeight:"394",
            //sticky display settings
            showOpener:"yes",
            showOpenerPlayPauseButton:"yes",
            verticalPosition:"bottom",
            horizontalPosition:"center",
            showPlayerByDefault:"yes",
            animatePlayer:"yes",
            openerAlignment:"right",
            mainBackgroundImagePath:"<?php echo Uri::root(true) . '/components/com_splms/assets/images/video-player/minimal_skin_dark/main-background.png'; ?>",
            openerEqulizerOffsetTop:-1,
            openerEqulizerOffsetLeft:3,
            offsetX:0,
            offsetY:0,
            //embed window
            showEmbedButton:"no",
            embedWindowCloseButtonMargins:15,
            borderColor:"#333333",
            mainLabelsColor:"#FFFFFF",
            secondaryLabelsColor:"#a1a1a1",
            shareAndEmbedTextColor:"#5a5a5a",
            inputBackgroundColor:"#000000",
            inputColor:"#FFFFFF",
            //ads
            openNewPageAtTheEndOfTheAds:"no",
            adsSource:[],
            adsButtonsPosition:"right",
            skipToVideoText:"You can skip to video in: ",
            skipToVideoButtonText:"Skip Ad",
            timeToHoldAds:4,
            adsTextNormalColor:"#999999",
            adsTextSelectedColor:"#FFFFFF",
            adsBorderNormalColor:"#666666",
            adsBorderSelectedColor:"#FFFFFF",
            //a to b loop
            useAToB:"no",
            atbTimeBackgroundColor:"transparent",
            atbTimeTextColorNormal:"#888888",
            atbTimeTextColorSelected:"#FFFFFF",
            atbButtonTextNormalColor:"#888888",
            atbButtonTextSelectedColor:"#FFFFFF",
            atbButtonBackgroundNormalColor:"#FFFFFF",
            atbButtonBackgroundSelectedColor:"#000000",
            //thumbnails preview
            thumbnailsPreview:"",
            thumbnailsPreviewWidth:196,
            thumbnailsPreviewHeight:110,
            thumbnailsPreviewBackgroundColor:"#000000",
            thumbnailsPreviewBorderColor:"#666",
            thumbnailsPreviewLabelBackgroundColor:"#666",
            thumbnailsPreviewLabelFontColor:"#FFF",
            // context menu
            showContextmenu:'yes',
            showScriptDeveloper:"no",
            contextMenuBackgroundColor:"#1f1f1f",
            contextMenuBorderColor:"#1f1f1f",
            contextMenuSpacerColor:"#333",
            contextMenuItemNormalColor:"#888888",
            contextMenuItemSelectedColor:"#FFFFFF",
            contextMenuItemDisabledColor:"#444",
            //loggin
            isLoggedIn:"yes",
            playVideoOnlyWhenLoggedIn:"yes",
            loggedInMessage:"Please login to view this video."
        });		
                
    });
</script>
<div class="splms-video-player-window">
    <div id="splmsVideoPlayer"></div>
</div>