/*!
 * REVOLUTION 6.0.0 EDITOR SLIDE JS
 * @version: 1.0 (01.07.2019)
 * @author ThemePunch
*/

(function() {
	var layerlistElementMarkup = '<div class="the_layers_in_slide" id="the_layers_in_slide_###">',
		atiw,
		pzdrag,
		slidesListSB,
		thbRptr;

	layerlistElementMarkup += '		<div class="resizeMainLayerListWrap" id="resizeMainLayerListWrap_###">';
	layerlistElementMarkup += '			<div class="mainLayerListWrap" id="mainLayerListWrap_###">';
	layerlistElementMarkup += '			</div>';
	layerlistElementMarkup += '		</div>';
	layerlistElementMarkup += '	</div>';


	/****************************************
		-	PUBLIC FUNCTIONS -
	****************************************/

	/*
	INITIALISE ALL LISTENERS AND INTERNAL FUNCTIONS FOR SLIDE EDITING/BUILDING
	*/
	RVS.F.initSlideBuilder = function() {
		thbRptr = jQuery('#slide_thumb_repeater');

		RVS.DOC.on('mouseenter','.slide_list_element',function() {
			thbRptr[0].innerHTML = "";
			if (RVS.SLIDER.settings.general.useWPML)
				jQuery('#slide_thumb_repeater').append(jQuery(this).find('.sle_thumb').clone());
			else
				jQuery('#slide_thumb_repeater').append(jQuery(this).find('.sle_thumb').first().clone());
			thbRptr.show();
		});
		RVS.DOC.on('mouseleave','.slide_list_element',function() {
			thbRptr[0].innerHTML = "";
		});

		createSlideAnimationList();
		initLocalInputBoxes();
		initLocalListeners();
		initKenBurnDrag();
	};



	RVS.F.changeFlags = function(_) {
		_ = RVS.SLIDER[RVS.S.slideId].slide.child;
		document.getElementById(RVS.S.slideId+'_flag_source').innerHTML = RVS.SLIDER.settings.general.useWPML && typeof RS_WPML_LANGS!=="undefined" && RS_WPML_LANGS!==undefined && _!==undefined && _.language!==undefined && _.language!=="" && _.language!==false && RS_WPML_LANGS[_.language]!==undefined ? '<span class="wpml_flag_wrap"><img src="'+RS_WPML_LANGS[_.language].image+'" class="wpml-img-flag" /></span>': '';
	}

	/*
	BUILD A SLIDE WHERE OBJ ATTRIBUTES EXISTING ALREADY
	*/
	RVS.F.addToSlideList = function(obj) {
		var _llem = layerlistElementMarkup.replace(/\###/g,RVS.S.slideId),
			_ = RVS.SLIDER[obj.id].slide,
			flag = RVS.SLIDER.settings.general.useWPML && typeof RS_WPML_LANGS!=="undefined" && RS_WPML_LANGS!==undefined && _.child!==undefined && _.child.language!==undefined && _.child.language!=="" && _.child.language!==false && RS_WPML_LANGS[_.child.language]!==undefined ? '<span id="'+obj.id+'_flag_source" class="flag_container aaa"><span class="wpml_flag_wrap"><img src="'+RS_WPML_LANGS[_.child.language].image+'" class="wpml-img-flag" /></span></span>' : '<span id="'+obj.id+'_flag_source" class="flag_container bbb"></span>',
			addchildslide =  typeof RS_WPML_LANGS!=="undefined" && RS_WPML_LANGS!==undefined ? '<div class="addchildslide" data-id="'+obj.id+'"><i class="material-icons">playlist_add</i></div>' : '',
			slideelement = _.static.isstatic ?
			    jQuery('<div id="slide_list_element_'+obj.id+'" class="do_not_sort_slide_list_element slide_list_element static-slide-btn" data-ref="'+obj.id+'"><div class="sle_description"><i class="material-icons">layers</i>'+_.title+'</div></div>') :
			    _.child===undefined || _.child.parentId===undefined || _.child.parentId==="" || _.child.parentId.length===0 || _.child.parentId===false ?
				jQuery('<li id="slide_list_element_'+obj.id+'" class="slide_list_element sortable_slide_list_element" data-ref="'+obj.id+'"><div class="slide_elemenet_content"><div class="sle_thumb"></div><div class="sle_description"><i class="material-icons">swap_vert</i>'+flag+'<span id="slide_list_element_title_index_'+obj.id+'"></span><span id="slide_list_element_title_'+obj.id+'">'+_.title+'</span></div><div class="slidetab_toolbox"><div id="publish_toggle_icon_'+obj.id+'" class="'+_.publish.state+'slide"><i class="publishedicon material-icons">visibility</i><i class="unpublishedicon material-icons">visibility_off</i></div><div class="deleteslide"><i class="material-icons">delete</i></div><div class="duplicateslide"><i class="material-icons">content_copy</i></div><div class="editslide" data-id="'+obj.id+'" ><i class="material-icons">settings</i></div>'+addchildslide+'</div>'+_llem+'</div><div id="slide_list_element_childwrap_'+obj.id+'" class="slide_list_child_element_wrap"></div></li>'):
				jQuery('<li id="slide_list_element_'+obj.id+'" class="slide_list_child_element" data-ref="'+obj.id+'"><div class="sle_thumb"></div><div class="slide_elemenet_content"><div class="sle_description">'+flag+'<span id="slide_list_element_title_'+obj.id+'">'+_.title+'</span></div><div class="slidetab_toolbox"><div id="publish_toggle_icon_'+obj.id+'" class="'+_.publish.state+'slide"><i class="publishedicon material-icons">visibility</i><i class="unpublishedicon material-icons">visibility_off</i></div><div class="deleteslide"><i class="material-icons">delete</i></div><div class="editslide" data-id="'+obj.id+'" ><i class="material-icons">settings</i></div></div>'+_llem+'</div></li>');

		var thmb = slideelement.find('.sle_thumb');
		updateSlideThumbs({id:obj.id, target:thmb});
		if (_.static.isstatic)
			slideelement.insertBefore('#slidelist');
		else {
			if (_.child.parentId!=="" && _.child.parentId!==undefined)
				jQuery('#slide_list_element_childwrap_'+_.child.parentId).append(slideelement);
			else
				jQuery('#slidelist').append(slideelement);
		}
		//slideelement.insertBefore('#slide_picker_wrap #newslide');
		makeSlideListSortable();

		if (slidesListSB===undefined)
			slidesListSB = jQuery('#slidelist').RSScroll({
				wheelPropagation:false,
				suppressScrollX:true,
				minScrollbarLength:100
			});
		else
			jQuery('#slidelist').RSScroll("update");
	};


	/* SINGLE
	GET NEXT AVAILABLE SLIDE ID AND RUN ADDREMOVESLIDEWITHBACKUP
	*/
	RVS.F.addRemoveSlideWithBackupAfterSlideId = function(params) {
		var amount = params.urls!==undefined ? params.urls.length : 1,
			temp = RVS.F.safeExtend(true,{},params.slideObj);
			temp.slide.child.parentId = params.parentId!==undefined ? params.parentId : "";

		RVS.F.ajaxRequest('create_slide', {slider_id:RVS.ENV.sliderID, amount:amount}, function(response){
			if(response.success) {
				for (var i in response.slide_id) {
					if(!response.slide_id.hasOwnProperty(i)) continue;
					params.slideId = response.slide_id[i];
					params.slideObj = RVS.F.safeExtend(true,{},temp);
					params.slideObj.id = params.slideObj.slide.uid = response.slide_id[i];

					if (params.urls!==undefined && params.urls.length>0) {
						params.slideObj.slide.bg.type="image";
						params.slideObj.slide.bg.image=params.urls[i].url;
						params.slideObj.slide.bg.imageSourceType="full";
						RVS.F.slideinWork(response.slide_id[i]);
					}
					RVS.F.addRemoveSlideWithBackup(params);
					if (params.parentID!==undefined || params.fromSlideId!==undefined) {
						RVS.F.convertIDStoTxt();
						RVS.F.saveSlides({index:0,slides:RVS.SLIDER.slideIDs, trigger:RVS.F.saveSliderSettings,works:RVS.SLIDER.inWork,force:true});
					}
					if (params.fromSlideId!==undefined) RVS.F.duplicateSkinColors({type:"slide", slideFrom:params.fromSlideId, slideTo:params.slideId});
					RVS.DOC.trigger('newSlideCreated', [response.slide_id[i]]);
				}
				if (params.endOfMain!==undefined) params.endOfMain();
			}
		});
	};



	/*
	CREATE NEW SLIDE ON DEMAND - DUPLICATE OR NEW SLIDE WITH BACKUP
	*/
	RVS.F.addRemoveSlideWithBackup = function(obj,index)	{
		RVS.F.openBackupGroup({id:obj.id,txt:obj.step,icon:obj.icon,lastkey:"#"+obj.slideId});
		 // Push Slide Object to Slider Array
		RVS.SLIDER[obj.slideId] = obj.slideObj;

		var _n = RVS.SLIDER.slideIDs.slice(),
			cache,
			mMO,
			focusindex,
			ssf = false;

		if (obj.id==="deleteslide") {
			var _deleteIndex = RVS.F._inArray(obj.slideId,RVS.SLIDER.slideIDs);
			focusindex = _deleteIndex-1 >=0 ? _deleteIndex-1 : _deleteIndex;

			if (RVS.S.slideId==obj.slideId) ssf = true;
			_n.splice(_deleteIndex,1);
			RVS.F.updateSliderObj({path:'slideIDs',val:_n});
			cache = jQuery('#slide_list_element_'+obj.slideId).removeClass("selected").detach();

		} else {
			_n.push(obj.slideId);
			RVS.F.updateSliderObj({path:'slideIDs',val:_n});
			RVS.F.addToSlideList({id:obj.slideId});
			mMO = {mode:"slidelayout",set:true, slide:obj.slideId};
		}
		RVS.F.backup({path:obj.slideId, cache:cache, beforeSelected:obj.beforeSelected, icon:obj.icon,txt:obj.step,lastkey:"#"+obj.slideId,force:true,val:RVS.F.safeExtend(true,{},RVS.SLIDER[obj.slideId]), old:obj.slideObjOld, backupType:"slide", bckpGrType:obj.id});

		if (jQuery('.slide_list_element.sortable_slide_list_element').length==0) mMO = {mode:"sliderlayout",set:true};

		if (mMO!==undefined) RVS.F.mainMode(mMO);
		else if (ssf) RVS.F.setSlideFocus({slideid:(focusindex>=RVS.SLIDER.slideIDs.length ? RVS.SLIDER.slideIDs[0] : RVS.SLIDER.slideIDs[focusindex])});
		RVS.F.closeBackupGroup({id:obj.id});
		if (obj.after!==undefined) obj.after();
	};




	/*
	SLIDE FOCUSED
	*/
	RVS.F.setSlideFocus = function(obj) {

		RVS.F.setEditorUrl(obj.slideid);
		RVS.F.slideinWork(obj.slideid);

		delete RVS.S.bgobj;

		RVS.DOC.trigger('beforeSlideChange');
		jQuery('.slide_list_element.selected, .slide_list_child_element.selected').removeClass("selected");
		jQuery('#slide_list_element_'+obj.slideid).addClass("selected");
		jQuery('.slide_li').hide(); // MOve to Else if want to show underlaying Slide also.

		if (RVS.SLIDER[obj.slideid].slide.static.isstatic) {
			window.lastSlideSettingForm = "static";
			RVS.F.updateStaticStartEndList();
			RVS.C.vW.addClass("staticlayersview");
			RVS.F.openSettings({forms:["*slidelayout**mode__slidestyle*#form_slidestatic"], uncollapse:true});
		} else {
			RVS.C.vW.removeClass("staticlayersview");
			if (window.lastSlideSettingForm === "static") {
				window.lastSlideSettingForm = jQuery('.slide_submodule_trigger.selected').data('forms');
				RVS.F.openSettings({forms:window.lastSlideSettingForm, uncollapse:true});
			}
		}
		RVS.DOC.trigger("slideAmountUpdated");

		RVS.S.slideId = obj.slideid;
		RVS.DOC.trigger('showLastEditedSlideStatic');
		RVS.DOC.trigger("slideFocusChanged");

		//CHECK SLIDE EXISTENS
		if (jQuery('#slide_'+obj.slideid).length===0) {

			// CREATE THE SLIDE HERE, AND LOAD ALL ELEMENTS ETC.
			var newSlide = jQuery('#slide_li_template').clone();
			newSlide.attr('id',"slide_"+obj.slideid);
			if (RVS.SLIDER[obj.slideid].slide.static.isstatic) newSlide.addClass("static_slide_li");
			newSlide.find('.crumb_title').html('<i class="material-icons">wallpaper</i>'+RVS.SLIDER[obj.slideid].slide.title);
			jQuery('#rev_slider_ul_inner').append(newSlide);
			RVS.TL[RVS.S.slideId] = RVS.TL[RVS.S.slideId]===undefined ? {} : RVS.TL[RVS.S.slideId];
		}



		RVS.C.slide = jQuery('#slide_'+obj.slideid);
		RVS.C.layergrid = RVS.C.slide.find('.layer_grid');
		RVS.DOC.trigger('sliderProgressUpdate');
		if (!window.contentDeltaFirstRun) RVS.F.updateContentDeltas();
		 RVS.C.rZone.top = RVS.C.layergrid.find('.row_wrapper_top');
		 RVS.C.rZone.middle = RVS.C.layergrid.find('.row_wrapper_middle');
		 RVS.C.rZone.bottom = RVS.C.layergrid.find('.row_wrapper_bottom');

		RVS.C.layergrid.attr("id","layer_grid_"+obj.slideid);
		RVS.H = {};

		RVS.C.slide.show();
		RVS.DOC.trigger('updatesliderlayout','setSlideFocus-139');
		RVS.F.setRulers();
		RVS.F.updateFields();

		//CALL BASIC CHANGES

		RVS.F.redrawSlideBG();

		RVS.F.updateParallaxLevelTexts();


		//Build Layers here
		RVS.F.buildLayerLists();
		RVS.F.updateAllLayerFrames();
		RVS.DOC.trigger('updateScrollBars');
		RVS.F.setRulers();
		RVS.DOC.trigger("updateAllInheritedSize");
		RVS.DOC.trigger("slideFocusFunctionEnd");
		setTimeout(function() {RVS.F.expandCollapseTimeLine(true,"open");},300);
		RVS.DOC.trigger('updateSlideLoopRange');
		RVS.DOC.trigger('updateFixedScrollRange');

		//Create a First Save Option to make sure that we have some Basic to compare
		RVS.S.lastSaved = RVS.S.lastSaved===undefined ? {} : RVS.S.lastSaved;
		if (RVS.S.lastSaved[RVS.S.slideId] === undefined && RVS.S.zIndexAtStartChanged!==true) {
			RVS.S.lastSaved[RVS.S.slideId] =
				{	params : JSON.stringify(RVS.F.simplifySlide(RVS.SLIDER[RVS.S.slideId].slide)),
					layers : JSON.stringify(RVS.F.simplifyAllLayer(RVS.SLIDER[RVS.S.slideId].layers))
				};
			delete RVS.S.zIndexAtStartChanged;
		}

	};

	RVS.F.getAllSlidesBgDimension = function() {
		for (var i in RVS.SLIDER.slideIDs) {
			if (!RVS.SLIDER.slideIDs.hasOwnProperty(i)) continue;
			RVS.F.getSliderBgDimension(RVS.SLIDER.slideIDs[i]);
			RVS.F.slideinWork(RVS.SLIDER.slideIDs[i]);
		}
	}

	/*
	 IF CAROUSEL AND JUSTIFY WALL ENABLED, SLIDER BG DIMENSION NEED TO BE CALCULATED
	 */
	RVS.F.getSliderBgDimension = function(slideId) {
		RVS.allimages= RVS.allimages===undefined ? {src:[], dim:[]} : RVS.allimages;
		if (!RVS.F.JWALL()) return;
		slideId = slideId === undefined ? RVS.S.slideId : slideId;

		switch (RVS.SLIDER[slideId].slide.bg.type) {
			case "image":
			case "external":
				var src = RVS.SLIDER[slideId].slide.bg.type==="image" ? RVS.SLIDER[slideId].slide.bg.image : RVS.SLIDER[slideId].slide.bg.externalSrc;
				if (src!==RVS.S.lastLoadedBGImage) {

					RVS.S.lastLoadedBGImage = src;
					var index = jQuery.inArray(src,RVS.allimages.src);
					if (index>=0) {
						if (RVS.allimages.dim[index]!==undefined) {
							RVS.SLIDER[slideId].slide.bg.imageWidth = RVS.allimages.dim[index].w;
							RVS.SLIDER[slideId].slide.bg.imageHeight = RVS.allimages.dim[index].h;
							RVS.SLIDER[slideId].slide.bg.imageRatio = RVS.allimages.dim[index].r;
							RVS.DOC.trigger('device_area_dimension_update');
						}
					} else {
						var img = new Image();
						index = RVS.allimages.src.length;
						RVS.allimages.src[index] = src;
						img.onload=function() {
							RVS.allimages.dim[index] = {w:this.width, h:this.height, r: this.width/this.height};
							RVS.SLIDER[slideId].slide.bg.imageWidth = this.width;
							RVS.SLIDER[slideId].slide.bg.imageHeight = this.height;
							RVS.SLIDER[slideId].slide.bg.imageRatio = this.width/this.height;
							RVS.DOC.trigger('device_area_dimension_update');
						};
						img.onerror = function(e) {}
						img.src = RVS.allimages.src[index];
					}
				}
			break;
		}
	}

	/*
	DRAW THE BG OF THE CURRENT SLIDE
	*/
	RVS.F.redrawSlideBG = function(forcereset) {
		if (RVS.C.slide===undefined) return;
		var _ = RVS.SLIDER[RVS.S.slideId].slide,
			slideBGFrom;

		for (var i in RVS.JHOOKS.redrawSlideBG) {
			if(!RVS.JHOOKS.redrawSlideBG.hasOwnProperty(i)) continue;
			slideBGFrom = RVS.JHOOKS.redrawSlideBG[i](slideBGFrom);
		}

		var bgobj = RVS.F.getSlideBGDrawObj({updateSip:true, slideBGFrom:slideBGFrom}),
			defimg = RVS.C.slide.find('.slotwrapper_cur .defaultimg');

		RVS.C.slide.find('.slots_wrapper').attr('class','slots_wrapper '+_.bg.mediaFilter);

		if (RVS.S.bgobj===undefined || bgobj["background"]!==RVS.S.bgobj["background"] || bgobj["backgroundImage"]!==RVS.S.bgobj["backgroundImage"] || bgobj["background-size"]!==RVS.S.bgobj["background-size"] || bgobj["backgroundColor"]!==RVS.S.bgobj["backgroundColor"] || bgobj["backgroundPosition"]!==RVS.S.bgobj["backgroundPosition"] || bgobj["backgroundRepeat"]!==RVS.S.bgobj["backgroundRepeat"]) {
			RVS.S.bgobj = RVS.F.safeExtend(true,{},bgobj);
			RVS.S.bgobj.mediaFilter = _.bg.mediaFilter;
			tpGS.gsap.set([defimg,jQuery('.inst-filter-griditem-img')],{backgroundImage:"none"});
			tpGS.gsap.set([defimg,jQuery('.inst-filter-griditem-img')],RVS.F.safeExtend(true,{},bgobj));
			updateSlideThumbs();
		}

		if (!RVS.TL.over && !RVS.TL.inDrag) {
			RVS.F.buildSlideAnimation({animation:RVS.F.getSlideAnimParams("transition")});
			RVS.F.slideAnimation({progress:1});
			//DISABLE KEN BURN IN CASE NOT NEEDED
			if (_.panzoom.set) {
				if (_.bg.type!=="image" && _.bg.type!=="external") disableKenBurn();
					else
				if (_.bg.lastLoadedImage !=undefined) RVS.F.buildKenBurn();
			}
		}

		RVS.F.getSliderBgDimension();
		RVS.DOC.trigger('redrawSlideBGDone');
		clearTimeout(window.redrawSlideBGTimeOut);
	};



	/*
	UPDATE THE INPUT FIELDS TO SHOW THE CURRENT SELECTED VALUES
	*/
	RVS.F.updateFields = function() {

		// Update Specials
		buildSlideToLinkDrop();

		//Reset Transition List
		RVS.F.updateSlideAnimation();

		//Update Fields
		RVS.F.updateEasyInputs({container:jQuery('.slide_settings_collector'), path:RVS.S.slideId+".slide.", trigger:"init"});
		jQuery('#s_bg_color').val(RVS.SLIDER[RVS.S.slideId].slide.bg.color).rsColorPicker("refresh");
		jQuery('#slide_bg_type').trigger('change');

		RVS.F.updateSlideBasedNavigationStyle();

		// Need New Pan Zoom Calculation ?
		//if (jQuery('#slide_'+RVS.S.slideId+" .slots_wrapper").data('kbtl')===undefined)
		updateKenBurnBasics();


		//Update TimeLine
		RVS.F.buildSlideFrames();
		RVS.F.updateSlideFrames();
		RVS.F.updateMaxTime({pos:true, cont:true});
		RVS.F.goToIdle();
	};

	/*
	GET THE CSS OBJECT OF A SLIDE THUMBNAIL
	*/
	RVS.F.getSlideBGDrawObj = function(obj) {
		obj = obj===undefined ? {updateSip:false} : obj;
		obj.id = obj.id===undefined ? RVS.S.slideId : obj.id;

		var _ = obj.slideBGFrom===undefined ? RVS.SLIDER[obj.id].slide : obj.slideBGFrom,
			bgobj = {
				backgroundImage:"",
				backgroundColor:"transparent",
				backgroundRepeat:_.bg.repeat,
				backgroundPosition:(_.bg.position==="percentage" ? parseInt(_.bg.positionX,0)+"% "+parseInt(_.bg.positionY,0)+"%" : _.bg.position),
				"background-size":(_.bg.fit==="percentage" ? parseInt(_.bg.fitX,0)+"% "+parseInt(_.bg.fitY,0)+"%" : _.bg.fit),
			},
			sip = jQuery('#slide_bg_image_path');

		switch (_.bg.type) {
			case "solid":
				var sbg = window.RSColor.get(_.bg.color);
				//BG COLOR OF SLIDER

				if (sbg.indexOf("gradient")>=0)
					bgobj = {background:sbg};
				else
					bgobj.backgroundColor = _.bg.color;
			break;
			case "trans":
			break;
			case "external":
				bgobj.backgroundImage = 'url('+_.bg.externalSrc+')';
				if (obj.updateSip) {
					sip.val(_.bg.externalSrc);
					sip.height(Math.max(25, (8 + ((_.bg.externalSrc.length/20) * 16))));
				}
			break;
			case "html5":
			case "vimeo":
			case "youtube":
			case "image":
				bgobj.backgroundImage = 'url('+_.bg.image+')';
				if (obj.updateSip) {
					sip.val(_.bg.image);
					if (_.bg.image!==undefined )
						sip.height(Math.max(25, (8 + ((_.bg.image.length/20) * 16))));
					else
					if (_.bg.image!==undefined )
						sip.height(Math.max(25, (8 + ((_.bg.image.length/20) * 16))));
				}
			break;
		}

		return bgobj;
	};


	/*
	GET THE SMALLEST SLIDE LENGTH BASED ON ADDED LAYERS AND CURRENT SELECTED TIME
	*/
	RVS.F.slideMinLength = function(v) {
		var _tempv = v;
		v = (v==="default" || v==="Default" || v===0 || v==="0ms") ? parseInt(RVS.SLIDER.settings.def.delay,0) : parseInt(v,0);

		var min = RVS.F.setSmallestSlideLength({left:v/10})*10;
		return  (_tempv==="Default" || _tempv===0 || _tempv==="0ms" || _tempv==="default") ? "Default" : min;
	};

	/*
	GET SLIDE LENGTH BASED ON DEFAULT AND EDITED VALUE
	*/
	RVS.F.getSlideLength = function() {
		var d = RVS.SLIDER[RVS.S.slideId].slide.timeline.delay;
		d = d == undefined || d=="" || d==="default" || d==0 || d==="Default"  ? RVS.SLIDER.settings.def.delay : d;
		d = d == undefined || d=="" || d==="default" || d==0 || d==="Default"  ? 8000 : parseInt(d,0);
		return d/10;
	};


	/*
	SLIDE ANIMATION HANDLINGS
	*/
	RVS.F.getSlideAnimParams = function(attribute) {
		//RVS.S.slideTrans
		var currentSelected = jQuery('#active_transitions_innerwrap li.selected').index();
		currentSelected = currentSelected===-1 ? 1 : currentSelected;
		var	r = RVS.SLIDER[RVS.S.slideId].slide.timeline[attribute][currentSelected];
		if (currentSelected===0)
			r = r=="default" && attribute=="duration" ? RVS.F.getSliderTransitionParameters(RVS.SLIDER[RVS.S.slideId].slide.timeline.transition[currentSelected]).TR[10] : r;
		else
			r = r=="default" && attribute=="duration" ? RVS.SLIDER[RVS.S.slideId].slide.timeline[attribute][currentSelected]===undefined ?  RVS.SLIDER.settings.def.transitionDuration : RVS.F.getSliderTransitionParameters(RVS.SLIDER[RVS.S.slideId].slide.timeline.transition[currentSelected]).TR[10] : r;
		return r;
	};

	/*
	BUILD THE SLIDE ANIMATION
	*/
	RVS.F.buildSlideAnimation = function(obj) {
		var a = RVS.C.slide.find('.slotwrapper_cur'),
			b = RVS.C.slide.find('.slotwrapper_prev');
		a.find('.slot').each(function() { jQuery(this).remove();});
		b.find('.slot').each(function() { jQuery(this).remove();});
		if (RVS.TL[RVS.S.slideId]!==undefined && RVS.TL[RVS.S.slideId].slide!== undefined) RVS.TL[RVS.S.slideId].slide.kill();

		/*tpGS.gsap.set(a,{filter:"blur(0px) grayscale(0%) brightness(100%)", "-webkit-filter":"blur(0px) grayscale(0%) brightness(100%)"});
		tpGS.gsap.set(b,{clearProps:"transform",filter:"blur(0px) grayscale(0%) brightness(100%)", "-webkit-filter":"blur(0px) grayscale(0%) brightness(100%)"});
		tpGS.gsap.set(a.find('.defaultimg'),{clearProps:"transform",filter:"blur(0px) grayscale(0%) brightness(100%)", "-webkit-filter":"blur(0px) grayscale(0%) brightness(100%)",autoAlpha:1});
		tpGS.gsap.set(b.find('.defaultimg'),{clearProps:"transform",filter:"blur(0px) grayscale(0%) brightness(100%)", "-webkit-filter":"blur(0px) grayscale(0%) brightness(100%)",autoAlpha:1});			*/
		obj.animation = obj.animation===undefined ?  RVS.F.getSlideAnimParams("transition") : obj.animation;
		RVS.TL[RVS.S.slideId] = RVS.TL[RVS.S.slideId]===undefined ? {} : RVS.TL[RVS.S.slideId];
		RVS.TL[RVS.S.slideId].slide = RVS.F.animateSlide(a, b,obj.animation,obj.MS);
	};

	/*
	SET THE SLIDE ANIMATION PROGRESS POSITION
	*/
	RVS.F.slideAnimation = function(obj) {
		if (RVS.TL[RVS.S.slideId].slide===undefined) return;
		if (obj.progress!==undefined) {
			RVS.TL[RVS.S.slideId].slide.progress(obj.progress);
			if (RVS.TL[RVS.S.slideId].panzoom) RVS.TL[RVS.S.slideId].panzoom.progress(0);
		}

	};


	/*
	SET THE FISRT ACTIVE TRANSITION SELECTED
	*/
	RVS.F.selectFirstActiveTransition = function() {
			var f = jQuery('li.added_slide_transition').first();
			jQuery('.added_slide_transition.selected').removeClass("selected");
			f.addClass("selected");
			RVS.S.slideTrans = 0;
			RVS.F.updateEasyInputs({container:jQuery('#active_transitions_settings'), path:RVS.S.slideId+".slide.", trigger:"init"});
			jQuery('#cur_transition_sub_settings').html(f.data('alias')+" Settings");
		};

	/****************************************
		-	INTERNAL FUNCTIONS -
	****************************************/

	function setActiveSlide(_) {

		var slidetab = jQuery('#slide_list_element_'+_.id);
		if (_.openclose) {
			if (!slidetab.hasClass("opened_slidetab")) {
				jQuery('.slide_list_element.sortable_slide_list_element').removeClass("opened_slidetab");
				slidetab.addClass("opened_slidetab");
				if (RVS.S.slideId!==_.id) RVS.F.setSlideFocus({slideid:_.id});
			} else {
				slidetab.removeClass("opened_slidetab");
			}
		} else

		if (RVS.S.slideId!==_.id) RVS.F.setSlideFocus({slideid:_.id});

		if (RVS.S.vWmode!=="mode__slidelayout") RVS.F.mainMode({mode:"slidelayout",set:false});
		RVS.F.showHideLayerEditor({mode:"slidelayout"});
		setTimeout(function() {
			RVS.DOC.trigger('sliderSizeChanged');
			RVS.DOC.trigger('device_area_availibity');
		},500);

	}
	/*
	INIT LOCAL INPUT BOX FUNCTIONS
	*/
	function initLocalInputBoxes() {

		RVS.DOC.on('changeflags',RVS.F.changeFlags);

		RVS.DOC.on('click','.editslide',function() {
			setActiveSlide({id:this.dataset.id});
			return false;
		});

		RVS.DOC.on('showLastEditedSlideStatic',function() {
			jQuery('.showunderstatic').removeClass("showunderstatic");
			if (RVS.SLIDER[RVS.S.slideId].slide.static.isstatic && RVS.S.lastShownSlideId!==undefined) {
				if (RVS.SLIDER[RVS.S.slideId].slide.static.lastEdited) {
					jQuery('#slide_'+RVS.S.lastShownSlideId).addClass("showunderstatic");
					jQuery('#slide_'+RVS.S.lastShownSlideId).find('._lc_.selected').removeClass("selected");
					setTimeout(function() {jQuery('#slide_'+RVS.S.slideId).addClass("hideslotsinslide");},200);
					// jQuery()
				} else {
					jQuery('#slide_'+RVS.S.slideId).removeClass("hideslotsinslide");
				}
			}
		});



		RVS.DOC.on('click','.open_close_slide',function() {
			setActiveSlide({id:this.dataset.id, openclose:true});
			return false;
		});

		// LISTEN TO ACTIVE SELECTED SLIDE TRANSITION
		RVS.DOC.on('click','.added_slide_transition',function() {
			jQuery('.added_slide_transition.selected').removeClass("selected");
			this.className = this.className+" selected";
			RVS.S.slideTrans = jQuery(this).index();
			RVS.F.updateEasyInputs({container:jQuery('#active_transitions_settings'), path:RVS.S.slideId+".slide.", trigger:"init"});
			jQuery('#cur_transition_sub_settings').html(this.dataset.alias+" Settings");
			RVS.F.updateSlideFrames();
		});

		RVS.DOC.on('click','.transition-replace',function() {
			window.replaceSlideAnimation = jQuery(this).closest('li.added_slide_transition');
			RVS.DOC.trigger("showhidetransitions");
		});

		//REMOVE ACTIVE TRANSITION
		RVS.DOC.on('click','.added_slide_transition .right-divided-icon',function() {
			removeTransitionToActive({this:jQuery(this).closest('li.added_slide_transition')});
			RVS.F.updateSlideFrames();
			return false;
		});

		// SLIDE TRANSITON GROUP CHANGE
		RVS.DOC.on('click','.transgroup',function() {
			jQuery('.transgroup.selected').removeClass("selected");
			this.className = this.className+" selected";
			//jQuery('#transition_selector_title').html(jQuery(this).find(".transgroup_name").text()+" Transitions");
			updateTransitionListe();
		});
		// SLIDE TRANSTION ADD ON
		RVS.DOC.on('click','.slide_trans_liste',function() {
			addTransitionToActive({slotable:this.dataset.slotable, rotatable:this.dataset.rotatable, handle:this.dataset.handle,selected:true, create:true});
			RVS.DOC.trigger("showhidetransitions");
			RVS.F.updateSlideFrames();
		});

		//
		RVS.DOC.on("mouseenter", '.slide_trans_liste.dark_btn',function(e) {
			clearTimeout(window.backToDefaultAnimationTimer);
			RVS.F.buildSlideAnimation({animation:this.dataset.handle,MS:"default"});
			RVS.TL[RVS.S.slideId].slide.play(0);
		});

		RVS.DOC.on("mouseleave",'.slide_trans_liste.dark_btn',function() {
			clearTimeout(window.backToDefaultAnimationTimer);
			window.backToDefaultAnimationTimer = setTimeout(function() {
				RVS.F.buildSlideAnimation({animation:RVS.SLIDER[RVS.S.slideId].slide.timeline.transition[RVS.S.slideTrans]});
				RVS.F.slideAnimation({progress:1});
			},100);
		});

	}


	function buildSlideToLinkDrop() {
		var linktoslide = jQuery('#slide_seo_linktoslide');
		linktoslide.html("");
		linktoslide.append('<option value="nothing">- Not Choosen -</option>');
		linktoslide.append('<option value="next">- Next Slide  -</option>');
		linktoslide.append('<option value="prev">- Previous Slide -</option>');
		linktoslide.append('<option value="scroll_under">- Scroll Below Slider -</option>');
		for (var slides in RVS.SLIDER.slideIDs) {
			if(!RVS.SLIDER.slideIDs.hasOwnProperty(slides)) continue;
			var nu = RVS.SLIDER.slideIDs[slides],
				tx = RVS.SLIDER[nu].slide.title;
			tx = tx===undefined ? "Slide" : tx;
			linktoslide.append('<option value="'+nu+'">'+tx+' (ID:'+nu+')</option>');

		}
	}

	/*
	INIT CUSTOM EVENT LISTENERS FOR TRIGGERING FUNCTIONS
	*/
	function initLocalListeners() {
		//RVS.DOC.on('',function() {);

		RVS.DOC.on('updateslidebasic',function(e,param) {RVS.F.redrawSlideBG(param!=="double" ? "force" : false);});
		RVS.DOC.on('coloredit colorcancel',colorEditSlider);
		RVS.DOC.on('showSlideFilter',tempSlideFilter);
		RVS.DOC.on('updateKenBurnBasics',function() {
			updateKenBurnBasics();
		});

		RVS.DOC.on('updateKenBurnSettings',updateKenBurnSettings);
		RVS.DOC.on('previewKenBurn',function() {updateKenBurnSettings(); RVS.F.updateTimeLine({state:"play",timeline:"panzoom"});});
		RVS.DOC.on('previewStopKenBurn',function() { updateKenBurnSettings();RVS.F.updateTimeLine({state:"stop",timeline:"panzoom"});});

		RVS.DOC.on('rewindKenBurn',function() { updateKenBurnSettings();RVS.F.updateTimeLine({state:"rewind",timeline:"panzoom"});});
		RVS.DOC.on('beforeLayoutModeChange accordionaction',function() {
			RVS.F.updateTimeLine({state:"stop",timeline:"panzoom"});
			RVS.F.changeSwitchState({el:jQuery('#kenburn_simulator')[0],state:"play"});
		});
		RVS.DOC.on('updateslidethumbs',function() {
			updateSlideThumbs();
		});
		RVS.DOC.on('resetslideadminthumb',function(e,p) {
			RVS.F.updateSliderObj({path:RVS.S.slideId+"."+p,val:""});
			updateSlideThumbs();
		});

		RVS.DOC.on('changeToLayerMode',function() { RVS.F.showHideLayerEditor({mode:"slidecontent"});});
		RVS.DOC.on('changeToSlideMode',function() { RVS.F.showHideLayerEditor({mode:"slidelayout"});});

		RVS.DOC.on("windowresized",function() {
			RVS.F.redrawSlideBG(true);
		});

		RVS.DOC.on('sliderSizeChanged',function() {
			RVS.F.redrawSlideBG(true);

		});

		RVS.DOC.on('showhidetransitions',function() {
			var ts = jQuery('#transition_selector');
			if (ts.is(':visible'))
				ts.hide();
			else
				ts.show();
		});

		RVS.DOC.on('updateSlideNameInList',function() {
			jQuery('#slide_list_element_title_'+RVS.S.slideId).html(RVS.SLIDER[RVS.S.slideId].slide.title);
		});

		RVS.DOC.on('click','#do_edit_slidename',function() {
			jQuery('#slide_title_field').focus();
		});


	}


	/*
	SHOW HIDE LAYER / SLIDE EDITOR MODE
	*/
	RVS.F.showHideLayerEditor = function(obj) {
		var selected;
		RVS.eMode = RVS.eMode===undefined ? {top:"", menu:""} : RVS.eMode;
		if (obj.mode === "slidecontent") {
			RVS.C.vW.addClass('mode__slidecontent');
			RVS.C.vW.removeClass('mode__slidestyle');
			RVS.eMode.top = "layer";
			selected = jQuery('.layer_submodule_trigger.selected');
			if (selected!==undefined && obj.openSettings!==false) RVS.F.openSettings({forms:selected.data("forms"), uncollapse:selected[0].dataset.collapse});
		} else {
			RVS.C.vW.removeClass('mode__slidecontent');
			RVS.C.vW.addClass('mode__slidestyle');
			RVS.eMode.top = "slide";
			selected  = jQuery('.slide_submodule_trigger.selected');
		}
		if (selected!==undefined && selected.length>=1 && selected.data("forms")!==undefined) RVS.eMode.menu = selected.data("forms")[0];
	};



	/*
	INIT KEN BURN DRAG FUNCTION
	*/
	function initKenBurnDrag() {
		pzdrag = {	container:jQuery('#kenburn_timeline')};
		pzdrag.pin = pzdrag.container.find('.pz_pin');
		pzdrag.done = pzdrag.container.find('.pz_timedone');
		pzdrag.pinWidth = 	9;
		pzdrag.hovered = false;

		pzdrag.pin.draggable({
			axis:"x",
			containment: "parent",
			start:function(event,ui) {
				pzdrag.container.addClass("indrag");
				pzdrag.containerWidth = pzdrag.container.width();
			},
			stop:function(event,ui) {
				pzdrag.container.removeClass("indrag");
			},
			drag:function(event,ui) {
				updatePzTimeDone({left:ui.position.left, force:true});
				RVS.F.updateTimeLine({state:"progress",timeline:"panzoom",prgs:ui.position.left / (pzdrag.containerWidth -pzdrag.pinWidth)});
			}
		});
		pzdrag.container.hover(function() {
			pzdrag.hovered = true;
			pzdrag.laststate = RVS.F.updateTimeLine({state:"getstate",timeline:"panzoom"});
			RVS.F.updateTimeLine({state:"pause",timeline:"panzoom"});
		}, function() {
			pzdrag.hovered = false;
			if (pzdrag.laststate)
				RVS.F.updateTimeLine({state:"play",timeline:"panzoom"});
		});
	}

	function updatePzTimeDone(obj) {
		if (pzdrag.hovered===false || obj.force===true) {
			tpGS.gsap.set(pzdrag.done,{width:obj.left});
			if (obj.auto)
				tpGS.gsap.set(pzdrag.pin,{left:obj.left});
		}
	}

	/*
	UPDATE THE THUMBS IN THE SLIDE (ADMIN AND NAVIGATION)
	*/
	function updateSlideThumbs(obj) {

		obj = obj===undefined ? {id:RVS.S.slideId, target:['#admin_purpose_thumbnail, #slide_list_element_'+RVS.S.slideId+' .sle_thumb'], default:true} : obj;

		var adminsrc = RVS.SLIDER[obj.id].slide.thumb.customAdminThumbSrc,
			navsrc = RVS.SLIDER[obj.id].slide.thumb.customThumbSrc;

		if (adminsrc===null || adminsrc==null || adminsrc===undefined || adminsrc.length<3) {
			var thmbbg = RVS.F.getSlideBGDrawObj(obj);
			if (thmbbg.backgroundImage==="" && thmbbg.backgroundColor==="transparent") {
				thmbbg.backgroundImage =  'url('+RVS.ENV.plugin_url+"admin/assets/images/trans_tile.png"+')';
				thmbbg.backgroundSize = "16px";
				thmbbg['background-size'] = "16px";
				thmbbg.backgroundRepeat = "repeat";
			}
			if (obj.target!==undefined && obj.target[0]!==undefined) tpGS.gsap.set(obj.target,thmbbg);
		}
		else
			tpGS.gsap.set(obj.target,{"background-size":"cover", backgroundPosition:"center center", backgroundRepeat:"no-repeat",backgroundImage:'url('+adminsrc+')'});



		if (obj.default) {
			if (navsrc===undefined || navsrc.length<3 || navsrc[navsrc.length-1]==="/")
				tpGS.gsap.set(["#navigation_purpose_thumbnail","#thumbs_"+obj.id,"#tabs_"+obj.id,"#bullets_"+obj.id,"#arrow_"+obj.id],RVS.F.getSlideBGDrawObj());
			else
				tpGS.gsap.set(["#navigation_purpose_thumbnail","#thumbs_"+obj.id,"#tabs_"+obj.id,"#bullets_"+obj.id,"#arrow_"+obj.id],{"background-size":"cover", backgroundPosition:"center center", backgroundRepeat:"no-repeat",backgroundImage:'url('+navsrc+')'});
		}
	}

	/**
	UPDATE SORTABLE FEATURE OF SLIDE LIST
	**/
	function makeSlideListSortable() {
		var sl = jQuery('#slidelist');
		if (sl.hasClass("ui-sortable")) sl.sortable('destroy');

		indexSlides();
		sl.sortable({
			item:".sortable_slide_list_element",
			cancel:"#theslidermodule, #newslide, .do_not_sort_slide_list_element",
			start:function(event,ui) {
				// var nodes = Array.prototype.slice.call(document.getElementById("slidelist").getElementsByClassName("sortable_slide_list_element"));
				RVS.C.vW.addClass("slides_in_sort");
				sl.sortable("refreshPositions");
			},
			stop:function(event,ui) {
				RVS.C.vW.removeClass("slides_in_sort");
				var nodes = Array.prototype.slice.call(document.getElementById("slidelist").getElementsByClassName("sortable_slide_list_element")),
					_nn = [],
					stat = "";
				for (var sti in RVS.SLIDER.slideIDs) {
					if(!RVS.SLIDER.slideIDs.hasOwnProperty(sti)) continue;
					if ((""+RVS.SLIDER.slideIDs[sti]).indexOf("static_")>=0) stat = RVS.SLIDER.slideIDs[sti];
				}
				for (var i in nodes) {
					if(!nodes.hasOwnProperty(i)) continue;
					_nn.push(nodes[i].dataset.ref);
				}
				_nn.push(stat);
				RVS.F.updateSliderObj({path:'slideIDs',val:_nn});
				indexSlides();
			}
		});
	}

	/*
	INDEXING THE SLIDES
	*/
	function indexSlides() {
		var indexes = {},
			_index=1;
		for (var ind in RVS.SLIDER.slideIDs) {
			if(!RVS.SLIDER.slideIDs.hasOwnProperty(ind)) continue;
			indexes[RVS.SLIDER.slideIDs[ind]] = _index;
			if ((jQuery.isNumeric(RVS.SLIDER.slideIDs[ind]) || RVS.SLIDER.slideIDs[ind].indexOf("static")==-1) && (RVS.SLIDER[RVS.SLIDER.slideIDs[ind]]!==undefined && RVS.SLIDER[RVS.SLIDER.slideIDs[ind]].slide!==undefined && RVS.SLIDER[RVS.SLIDER.slideIDs[ind]].slide.child!==undefined && (RVS.SLIDER[RVS.SLIDER.slideIDs[ind]].slide.child.parentId==undefined || RVS.SLIDER[RVS.SLIDER.slideIDs[ind]].slide.child.parentId==""))) _index++;
		}

		for (var ind in indexes) {
			if(!indexes.hasOwnProperty(ind)) continue;
			var el = document.getElementById('slide_list_element_title_index_'+ind);
			if (el!==null && el!==undefined) el.innerHTML = "#"+indexes[ind]+" ";
		}

	}

	function FOtoA(a) {
		//If Multiple Values written like this:  {1:"fade",2:"parallax"....} convert them to array: ["fade","parallax"....]
		if (typeof a==="object" && !jQuery.isArray(a) && ((a[0]!==undefined && (typeof a[0]==="string" || typeof a[0]==="number")) || (a[1]!==undefined && (typeof a[1]==="string" || typeof a[1]==="number")))) a = Object.values(a);
		return a;
	}

	function FCV(b) {
		return jQuery.isArray(b) && typeof b[0]!=='object' ?  b : typeof b[0]==="object" ? Object.values(b[0]) : [b];
	}

	/**
	SLIDE ANIMATION SETTINGS
	**/
	RVS.F.updateSlideAnimation = function() {
		atiw = atiw===undefined ? jQuery('#active_transitions_innerwrap') : atiw;
		if (atiw.hasClass('.ui-sortable'))
			atiw.sortable("destroy");
		atiw.html('');
		RVS.S.slideTrans = 0;
		updateTransitionListe();
		RVS.F.updateEasyInputs({container:jQuery('#active_transitions_settings'), path:RVS.S.slideId+".slide.", trigger:"init"});
		var _ = RVS.SLIDER[RVS.S.slideId].slide.timeline;

		_.duration = FCV(FOtoA(_.duration));
		_.rotation = FCV(FOtoA(_.rotation));
		_.slots = FCV(FOtoA(_.slots));
		_.transition = FCV(FOtoA(_.transition));
		if (_.transition!==undefined && _.transition[0]!==undefined && _.transition[0]==="") _.transition[0]="fade";

		for (var ti in _.transition) {
			if(!_.transition.hasOwnProperty(ti)) continue;
			addTransitionToActive({handle:_.transition[ti], selected:ti==0});
		}
		atiw.sortable({
			start:function(event,ui) {
				atiw[0].dataset.fromIndex = ui.item.index();
			},
			stop:function(event,ui) {
				var _from = atiw[0].dataset.fromIndex,
					_to = ui.item.index(),
					_n = RVS.F.safeExtend(true,{},_);
				_n.transition = RVS.F.amove(_n.transition,_from,_to);
				_n.duration = RVS.F.amove(_n.duration,_from,_to);
				_n.easeIn = RVS.F.amove(_n.easeIn,_from,_to);
				_n.easeOut = RVS.F.amove(_n.easeOut,_from,_to);
				_n.rotation = RVS.F.amove(_n.rotation,_from,_to);
				_n.slots = RVS.F.amove(_n.slots,_from,_to);
				recordSlideAnimationChanges(_n);
			}
		});

	}

	function createSlideAnimationList() {
		var ts = jQuery('#transition_selector');
		for (var i in RVS.LIB.SLIDEANIMS) {
			if(!RVS.LIB.SLIDEANIMS.hasOwnProperty(i)) continue;
			var extraclass = i===0 ? " selected" : "";
			var group = '<div class="transgroup '+extraclass+'" data-group="'+i+'"><span class="transgroup_name">'+RVS.LIB.SLIDEANIMS[i].alias+'</span><div class="animation_drop_arrow"><i class="material-icons">arrow_drop_down</i></div><div class="'+i+' inner_transitions">';
			for (var j in RVS.LIB.SLIDEANIMS[i]) {
				if(!RVS.LIB.SLIDEANIMS[i].hasOwnProperty(j)) continue;
				if (j!=="alias")
					group +='<div data-handle="'+j+'" class="slide_trans_liste dark_btn '+i+'">'+RVS.LIB.SLIDEANIMS[i][j]+'<i class="right-divided-icon material-icons">add</i></div>';
			}
			group +='</div></div>';
			ts.append(group);
		}
	}
	function recordSlideAnimationChanges(_) {
		// Record it for the Undo/Redo function also
		RVS.F.openBackupGroup({id:"slidetransitionarrays",txt:_.txt,icon:_.icon});
		RVS.F.updateSliderObj({path:RVS.S.slideId+'.slide.timeline.transition',val:_.transition});
		RVS.F.updateSliderObj({path:RVS.S.slideId+'.slide.timeline.duration',val:_.duration});
		RVS.F.updateSliderObj({path:RVS.S.slideId+'.slide.timeline.easeIn',val:_.easeIn});
		RVS.F.updateSliderObj({path:RVS.S.slideId+'.slide.timeline.easeOut',val:_.easeOut});
		RVS.F.updateSliderObj({path:RVS.S.slideId+'.slide.timeline.rotation',val:_.rotation});
		RVS.F.updateSliderObj({path:RVS.S.slideId+'.slide.timeline.slots',val:_.slots});
		RVS.F.closeBackupGroup({id:"slidetransitionarrays"});
	}

	function getSlideTransitionAlias(a) {
		var ret;
		// for (var i in RVS.LIB.SLIDEANIMS) if (!ret) for (var j in RVS.LIB.SLIDEANIMS[i]) if (!ret && j===a) ret={alias:RVS.LIB.SLIDEANIMS[i][j], group:i}
		for (var i in RVS.LIB.SLIDEANIMS) {
			if(!RVS.LIB.SLIDEANIMS.hasOwnProperty(i)) continue;
			if (!ret) {
				for (var j in RVS.LIB.SLIDEANIMS[i]) {
					if(!RVS.LIB.SLIDEANIMS[i].hasOwnProperty(j)) continue;
					if (!ret && j===a) {
						ret={alias:RVS.LIB.SLIDEANIMS[i][j], group:i};
						break;
					}
				}
			}
			else {
				break;
			}
		}
		return ret;
	}

	function convertToArray(a) {
		if (typeof a === "object" && !Array.isArray(a))
			return Object.keys(a).map(function(key) { return [a[key]];});
		else
		return a;
	}

	/**
	SLIDE ANIMATION CREATE / ADD ACTIVE ANIMATION TO LIST
	**/
	function addTransitionToActive(obj) {
		if (obj.selected) jQuery('.dark_btn.added_slide_transition.selected').removeClass("selected");
		var slidetrans = getSlideTransitionAlias(obj.handle);
		if (window.replaceSlideAnimation!==undefined) {
			window.replaceSlideAnimationIndex = window.replaceSlideAnimation.index();
			jQuery('<li data-slotable="" data-rotatable="" data-handle="'+obj.handle+'" data-alias="'+slidetrans.alias+'" class="'+(obj.selected ? "selected" : "")+' dark_btn added_slide_transition '+slidetrans.group+'">'+slidetrans.alias+'<i class="transition-replace material-icons">settings</i><i class="transition-remove right-divided-icon material-icons">close</i></li>').insertAfter(window.replaceSlideAnimation);
			window.replaceSlideAnimation.remove();
			delete window.replaceSlideAnimation;
		} else{
			atiw.append('<li data-slotable="" data-rotatable="" data-handle="'+obj.handle+'" data-alias="'+slidetrans.alias+'" class="'+(obj.selected ? "selected" : "")+' dark_btn added_slide_transition '+slidetrans.group+'">'+slidetrans.alias+'<i class="transition-replace material-icons">settings</i><i class="transition-remove right-divided-icon material-icons">close</i></li>');
		}
		if (obj.create) {
			var _n = RVS.F.safeExtend(true,{},RVS.SLIDER[RVS.S.slideId].slide.timeline);
			if (window.replaceSlideAnimationIndex!==undefined) {
				_n.transition[window.replaceSlideAnimationIndex] = obj.handle;
				_n.duration[window.replaceSlideAnimationIndex] = "default";
				_n.easeIn[window.replaceSlideAnimationIndex] = "default";
				_n.easeOut[window.replaceSlideAnimationIndex] = "default";
				_n.rotation[window.replaceSlideAnimationIndex] = "default";
				_n.slots[window.replaceSlideAnimationIndex] = "default";
				delete window.replaceSlideAnimationIndex;
			} else {

				_n.transition = convertToArray(_n.transition);
				_n.duration = convertToArray(_n.duration);
				_n.easeIn = convertToArray(_n.easeIn);
				_n.easeOut = convertToArray(_n.easeOut);
				_n.rotation = convertToArray(_n.rotation);
				_n.slots = convertToArray(_n.slots);

				_n.transition.push(obj.handle);
				_n.duration.push("default");
				_n.easeIn.push("default");
				_n.easeOut.push("default");
				_n.rotation.push("default");
				_n.slots.push("default");
			}
			_n.txt = "Add Active Slide Transition";
			_n.icon = "add_circle";
			recordSlideAnimationChanges(_n);
		}
		if (obj.selected) {
			RVS.S.slideTrans = jQuery('.dark_btn.added_slide_transition.selected').index();
			RVS.F.updateEasyInputs({container:jQuery('#active_transitions_settings'), path:RVS.S.slideId+".slide.", trigger:"init"});
			jQuery('#cur_transition_sub_settings').html(slidetrans.alias+" Settings");
		}
	}

	/**
	REMOVE ANIMATION FROM ACTIVE LIST
	**/
	function removeTransitionToActive(obj) {

		if (jQuery('#active_transitions_innerwrap li').length===1) return;
		var _ = RVS.F.safeExtend(true,{},RVS.SLIDER[RVS.S.slideId].slide.timeline),
			i = obj.this.index();
		_.transition.splice(i,1);
		_.duration.splice(i,1);
		_.easeIn.splice(i,1);
		_.easeOut.splice(i,1);
		_.rotation.splice(i,1);
		_.slots.splice(i,1);
		_.txt = "Remove Active Slide Transition";
		_.icon = "remove_circle";
		recordSlideAnimationChanges(_);

		obj.this.remove();
		RVS.F.selectFirstActiveTransition();
	}



	/**
	UPDATE TRANSITION LIST NOW
	**/
	function updateTransitionListe() {
		var group = jQuery('.transgroup.selected').data('group');
		jQuery('.inner_transitions').hide();
		jQuery('.inner_transitions.'+group).show();
	}


	/*
	TEMPORARY FILTER FOR SLIDE IMAGE
	*/
	function tempSlideFilter(e,param) {
		RVS.C.slide.find('.slots_wrapper').attr('class','slots_wrapper '+param);
	}

	function colorEditSliderSub(n,val,c) {
		switch (n) {
			case "slide_bg_color":	if (c) RVS.F.redrawSlideBG(); else tpGS.gsap.set(RVS.C.slide.find('.slotwrapper_cur .defaultimg'),{background:val});break;
		}
	}

	/*
	EDIT / CANCEL A COLOR VALUE (SHOW LIVE THE CHANGES)
	*/
	function colorEditSlider(e,inp, val,gradient,onSave,GC) {
		var canceled = false;
		if (inp!==undefined) window.lastColorEditjObj = jQuery(inp);
		else {
			if (window.lastColorEditjObj!==undefined) val = window.RSColor.get(window.lastColorEditjObj.val());
			canceled = true;
		}
		if (val===undefined) return;
		colorEditSliderSub(window.lastColorEditjObj[0].name, val,canceled)
		if (GC && canceled!==true)
			for (var i in GC.ref) {
				if (GC.ref[i].type==="slide" && GC.ref[i].slide===RVS.S.slideId) colorEditSliderSub(GC.ref[i].inpname,val);
				if (GC.ref[i].type==="slide" && onSave) {
					RVS.F.updateSliderObj({path:GC.ref[i].r,val:val});
					if (GC.ref[i].slide===RVS.S.slideId) {
						var upinp = jQuery('input[name='+GC.ref[i].inpname+']');
						if (upinp.length>0) {
							upinp[0].value = val;
							upinp.rsColorPicker("refresh");
						}
					} else RVS.F.slideinWork(GC.ref[i].slide);
				}
			}
	}



	/***
	PAN ZOOM MAGIC
	***/



	/*
	MOVE KEN BURN SETTINGS FROM ONE CONTAINER TO AN OTHER
	*/
	function updateKenBurnBasics() {
		if (RVS.SLIDER[RVS.S.slideId].slide.panzoom.set) {
			jQuery('#slide_bg_settings_wrapper').appendTo(jQuery('#ken_burn_bg_setting_on'));
			RVS.F.buildKenBurn();
		} else {
			jQuery('#slide_bg_settings_wrapper').appendTo(jQuery('#ken_burn_bg_setting_off'));
			removeKenBurn();
		}

	}



	/*
	DISABLE KEN BURN
	*/
	function disableKenBurn() {
		RVS.F.setInputTo({field:jQuery('#sl_pz_set'), val:false, path:'#slide#.slide'});
		updateKenBurnBasics();
	}

	/*
	REMOVE KEN BURN FROM STAGE
	*/
	function removeKenBurn(obj) {
		obj = obj===undefined ? {id:RVS.S.slideId} : obj;
		var l = jQuery('#slide_'+obj.id+" .slots_wrapper");
		if (RVS.TL[obj.id]!==undefined && RVS.TL[obj.id].panzoom!=undefined)
			RVS.TL[obj.id].panzoom.pause();
		l.find('.rs-pzimg').remove();
		RVS.TL[obj.id].panzoom = undefined;
	}

	/*
	LOAD IMAGE AND BUILD KEN BURN
	*/
	RVS.F.buildKenBurn = function(obj) {

		if (!RVS.SLIDER[RVS.S.slideId].slide.panzoom.set) return;
		jQuery('#internal_kenburn_settings').hide();
		jQuery('#kenburnissue').hide();
		var img = new Image(),
			_ = RVS.SLIDER[RVS.S.slideId].slide,
			w = jQuery('#slide_'+RVS.S.slideId+" .slots_wrapper"),
			nsrc = _.bg.type==="external" ? _.bg.externalSrc : _.bg.image,
			inloadalready = w.data('inload');

		if (!inloadalready) {
			if (_.bg.lastLoadedImage===undefined || nsrc!==_.bg.lastLoadedImage.src) {
				w.data('inload',true);
				img.onload = function() {
					_.bg.lastLoadedImage = {width:this.width, height:this.height, src:this.src};
					w.data('inload',false);
					removeKenBurn();
					RVS.F.kenBurnTimeline();
					jQuery('#internal_kenburn_settings').show();
				};
				img.onerror = function() {
					disableKenBurn();
					jQuery('#kenburnissue_info').html(RVS_LANG.imageCouldNotBeLoaded);
					jQuery('#kenburnissue').show();
					jQuery('.slide_submodule_trigger').one('click', function() {
						jQuery('#kenburnissue').hide();
					})
					w.data('inload',false);

				};
				img.onabort = function() {
					console.log("Pan Zoom Demo Image could not be Loaded");
					jQuery('#kenburnissue_info').html(RVS_LANG.imageCouldNotBeLoaded);
					jQuery('#kenburnissue').show();
					jQuery('.slide_submodule_trigger').one('click', function() {
						jQuery('#kenburnissue').hide();
					})
					disableKenBurn();
					w.data('inload',false);
				}	;
				img.src = nsrc;
			} else {
				jQuery('#internal_kenburn_settings').show();
				RVS.F.kenBurnTimeline();
			}
		}
	};

	/*
	UPDATE KENBURN TIMELINE WITH OUR WITHOUT PROGRESS
	*/
	function updateKenBurnSettings() {
		if (!RVS.SLIDER[RVS.S.slideId].slide.panzoom.set) return;
		if (RVS.TL[RVS.S.slideId].panzoom!=undefined)
			RVS.F.kenBurnTimeline({prgs:RVS.TL[RVS.S.slideId].panzoom.progress()});
		else
			RVS.F.kenBurnTimeline();
	}

	/*
	BUILD TIMELINE FOR KEN BURN
	*/
	RVS.F.kenBurnTimeline = function(obj) {
		RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
		RVS.F.buildMainTimeLine();
		RVS.F.updateCurTime({pos:true, cont:true, force:false, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});

		obj = obj===undefined ? {prgs:0.000001} : obj;
		var  _ = RVS.SLIDER[RVS.S.slideId].slide;

		var l = jQuery('#slide_'+RVS.S.slideId+" .slots_wrapper"),
			d = {lastsrc:_.bg.lastLoadedImage.src,
				owidth:_.bg.lastLoadedImage.width,
				oheight:_.bg.lastLoadedImage.height,
				bgposition:(_.bg.position==="percentage" ? _.bg.positionX+"% "+_.bg.positionY+"%" : _.bg.position),
				duration:parseInt(_.panzoom.duration,0),
				rotatestart:parseInt(_.panzoom.rotateStart,0),
				rotateend:parseInt(_.panzoom.rotateEnd,0),
				scalestart:parseInt(_.panzoom.fitStart,0),
				scaleend:parseInt(_.panzoom.fitEnd,0),
				offsetstart:_.panzoom.xStart+" "+_.panzoom.yStart,
				offsetend:_.panzoom.xEnd+" "+_.panzoom.yEnd,
				blurstart:parseInt(_.panzoom.blurStart,0),
				blurend:parseInt(_.panzoom.blurEnd,0),
				ease:_.panzoom.ease},
			s = d.lastsrc,
			/* i_a = d.owidth / d.oheight, */
			cw = l.width(),
			ch = l.height();
			/* c_a = cw / ch; */

		if (RVS.TL[RVS.S.slideId] && RVS.TL[RVS.S.slideId].panzoom) RVS.TL[RVS.S.slideId].panzoom.kill();
		obj.prgs = obj.prgs || 0.000001;

		if (s===undefined) return;

		if (l.find('.rs-pzimg').length===0) {
			l.find('.slotwrapper_cur').append('<rs-pzimg-wrap><img class="rs-pzimg" src="'+s+'" style="position:absolute;" width="'+obj.owidth+'" height="'+d.oheight+'"></rs-pzimg-wrap>');
			l.data('kenburn',l.find('.rs-pzimg'));
		}


		var getKBSides = function(w,h,f,cw,ch,ho,vo) {
					var tw = w * f,
						th = h * f,
						hd = Math.abs(cw-tw),
						vd = Math.abs(ch-th),
						s = {};
					s.l = (0-ho)*hd;
					s.r = s.l + tw;
					s.t = (0-vo)*vd;
					s.b = s.t + th;
					s.h = ho;
					s.v = vo;


					return s;
				},

			getKBCorners = function(d,cw,ch,ofs,o) {

				var p = d.bgposition.split(" ") || "center center",
					ho = p[0] == "center"  ? "50%" : p[0] == "left" || p [1] == "left" ? "0%" : p[0]=="right" || p[1] =="right" ? "100%" : p[0],
					vo = p[1] == "center" ? "50%" : p[0] == "top" || p [1] == "top" ? "0%" : p[0]=="bottom" || p[1] =="bottom" ? "100%" : p[1];

				ho = parseInt(ho,0)/100 || 0;
				vo = parseInt(vo,0)/100 || 0;


				var sides = {};


				sides.start = getKBSides(o.start.width,o.start.height,o.start.scale,cw,ch,ho,vo);
				sides.end = getKBSides(o.start.width,o.start.height,o.end.scale,cw,ch,ho,vo);

				return sides;
			},

			kcalcL = function(cw,ch,d) {
				var f=d.scalestart/100,
					fe=d.scaleend/100,
					ofs = d.offsetstart != undefined ? d.offsetstart.split(" ") || [0,0] : [0,0],
					ofe = d.offsetend != undefined ? d.offsetend.split(" ") || [0,0] : [0,0];
				d.bgposition = d.bgposition == "center center" ? "50% 50%" : d.bgposition;

				// var o = {start:{width:cw, height:cw / d.owidth * d.oheight},starto:{},end:{},endo:{}}, sw = cw*f, sh = sw/d.owidth * d.oheight,ew = cw*fe,eh = ew/d.owidth * d.oheight;
				var o = {start:{width:cw, height:cw / d.owidth * d.oheight},starto:{},end:{},endo:{}};

				if (o.start.height<ch) {
					o.start.width = o.start.width*(ch / o.start.height);
					o.start.height = ch;
				}

				o.start.transformOrigin = d.bgposition;
				o.start.scale = f;
				o.end.scale = fe;

				o.start.rotation = d.rotatestart+"deg";
				o.end.rotation = d.rotateend+"deg";

				// MAKE SURE THAT OFFSETS ARE NOT TOO HIGH
				var c = getKBCorners(d,cw,ch,ofs,o);

				ofs[0] = parseFloat(ofs[0]) + c.start.l;
				ofe[0] = parseFloat(ofe[0]) + c.end.l;



				ofs[1] = parseFloat(ofs[1]) + c.start.t;
				ofe[1] = parseFloat(ofe[1]) + c.end.t;

				var iws = c.start.r - c.start.l,
					ihs	= c.start.b - c.start.t,
					iwe = c.end.r - c.end.l,
					ihe	= c.end.b - c.end.t;


				// X (HORIZONTAL)

				ofs[0] = ofs[0]>0 ? 0 : iws + ofs[0] < cw ? cw-iws : ofs[0];
				ofe[0] = ofe[0]>0 ? 0 : iwe + ofe[0] < cw ? cw-iwe : ofe[0];
				o.starto.x = ofs[0]+"px";
				o.endo.x = ofe[0]+"px";

				// Y (VERTICAL)
				ofs[1] = ofs[1]>0 ? 0 : ihs + ofs[1] < ch ? ch-ihs : ofs[1];
				ofe[1] = ofe[1]>0 ? 0 : ihe + ofe[1] < ch ? ch-ihe : ofe[1];
				o.starto.y = ofs[1]+"px";
				o.endo.y = ofe[1]+"px";
				o.end.ease = o.endo.ease = d.ease;
				o.end.force3D = o.endo.force3D = true;


				return o;
			};

		if (RVS.TL[RVS.S.slideId]!==undefined && RVS.TL[RVS.S.slideId].panzoom!=undefined) {
			RVS.TL[RVS.S.slideId].panzoom.kill();
			delete RVS.TL[RVS.S.slideId].panzoom;
		}

		var k = l.data('kenburn'),
			kw = k.parent(),
			anim = kcalcL(cw,ch,d);
		RVS.TL[RVS.S.slideId] = RVS.TL[RVS.S.slideId]===undefined ? {} : RVS.TL[RVS.S.slideId];
		RVS.TL[RVS.S.slideId].panzoom =  tpGS.gsap.timeline();

		if (jQuery('#kenburn_simulator')[0].dataset.state==="play")
			RVS.TL[RVS.S.slideId].panzoom.pause();


		anim.start.transformOrigin = "0% 0%";
		anim.starto.transformOrigin = "0% 0%";

		RVS.TL[RVS.S.slideId].panzoom.add(tpGS.gsap.fromTo(k,d.duration/1000,anim.start,anim.end),0);
		RVS.TL[RVS.S.slideId].panzoom.add(tpGS.gsap.fromTo(kw,d.duration/1000,anim.starto,anim.endo),0);

		// ADD BLUR EFFECT ON THE ELEMENTS
		if (d.blurstart!==undefined && d.blurend!==undefined &&  (d.blurstart!==0 || d.blurend!==0 || RVS.TL[RVS.S.slideId].blurAnimation!==undefined)) {
			if (d.blurstart!==0 || d.blurend!==0) {
				var blurElement = {a:d.blurstart},
					blurElementEnd = {a:d.blurend, ease:anim.endo.ease};

				RVS.TL[RVS.S.slideId].blurAnimation = tpGS.gsap.to(blurElement, d.duration/1000, blurElementEnd);

				RVS.TL[RVS.S.slideId].blurAnimation.eventCallback("onUpdate", function(kw) {
					tpGS.gsap.set(kw,{filter:'blur('+blurElement.a+'px)',webkitFilter:'blur('+blurElement.a+'px)'});
				},[kw]);
			}
			RVS.TL[RVS.S.slideId].panzoom.add(tpGS.gsap.set(kw,{filter:'blur(0px)',webkitFilter:'blur(0px)'}),0);
			if (d.blurstart!==0 || d.blurend!==0)
				RVS.TL[RVS.S.slideId].panzoom.add(RVS.TL[RVS.S.slideId].blurAnimation,0);
			else delete RVS.TL[RVS.S.slideId].blurAnimation;
		}


		RVS.TL[RVS.S.slideId].panzoom.progress(obj.prgs);
		pzdrag.containerWidth= pzdrag.containerWidth===undefined ? pzdrag.container.width() : pzdrag.containerWidth;
		updatePzTimeDone({left: (obj.prgs *(pzdrag.containerWidth -pzdrag.pinWidth)),auto:true });


		RVS.TL[RVS.S.slideId].panzoom.eventCallback("onUpdate", function() {
			pzdrag.containerWidth= pzdrag.containerWidth===undefined ? pzdrag.container.width() : pzdrag.containerWidth;
			updatePzTimeDone({left: (RVS.TL[RVS.S.slideId].panzoom.progress() *(pzdrag.containerWidth -pzdrag.pinWidth)),auto:true });
		});

		RVS.TL[RVS.S.slideId].panzoom.eventCallback("onComplete", function() {
			RVS.F.changeSwitchState({el:jQuery('#kenburn_simulator')[0],state:"play"});
			RVS.TL[RVS.S.slideId].panzoom.pause();
		});



	};

	/*
	BUILD AND EXTEND DEFAULT SLIDE
	*/
	RVS.F.addSlideObj = function(obj,compare) {
		var empty = obj===undefined || jQuery.isEmptyObject(obj);
		obj = obj===undefined ? {} : obj;
		if (!empty && typeof _rmig_ !=="undefined") obj = _rmig_.migrateSlide(obj);
		var newSlide = {};
		newSlide.addOns = obj.addOns || {};
		newSlide.version = _d(obj.version,"6.0.0");
		newSlide.version = newSlide.version<"6.0.0" ? "6.0.0" : newSlide.version;
		newSlide.static = _d(obj.static,{
				isstatic:false,
				overflow:"hidden",
				position:"front",
				lastEdited:true
		});
		newSlide.runtime=_d(obj.runtime,{
			collapsedGroups:[]
		});
		newSlide.title = _d(obj.title,"New Slide");
		newSlide.child = _d(obj.child,{
			parentId:"",
			language:""
		});
		newSlide.bg = _d(obj.bg,{
			type : "trans",
			color:"#ffffff",
			externalSrc:"",
			fit:"cover",
			fitX:"100",
			fitY:"100",
			position:"center center",
			positionX:"0",
			positionY:"0",
			repeat:"no-repeat",
			image:"",
			imageId:"",
			imageFromStream:false,
			imageSourceType:"full",
			imageLib:"nothing",
			galleryType:"gallery",
			mpeg:"",
			ogv:"",
			webm:"",
			vimeo:"",
			youtube:"",
			mediaFilter:"none",
			video:{
				args:"",
				argsVimeo:"",
				dottedOverlay:"none",
				startAt:"",
				endAt:"",
				forceCover:false,
				forceRewind:true,
				loop:true,
				pausetimer:false,
				mute:true,
				nextSlideAtEnd:false,
				ratio:"16:9",
				speed:1,
				volume:0,
			},
			videoId:"",
			videoFromStream:false
		});

		// CHANGES, TO SET LOOP AND PAUSE TIMER INDEPENDENT, HAVING 4 CASES
		if (newSlide.bg.video!==undefined && compare!==undefined) {
			newSlide.bg.video.loop = newSlide.bg.video.loop===true || (obj!==undefined && obj.bg!==undefined && obj.bg.video!==undefined && (obj.bg.video.loop==="loopandnoslidestop" || obj.bg.video.loop==="loop" || obj.bg.video.loop===true || obj.bg.video.loop==="true")) ? true : false;
			newSlide.bg.video.pausetimer =obj.pausetimer!==undefined && (obj.pausetimer===true || obj.pausetimer===false) ? obj.pausetimer : obj!==undefined && obj.bg!==undefined && obj.bg.video!==undefined && obj.bg.video.loop === "loop"  ? true : false;
			if (newSlide.bg.video.loop===true && newSlide.bg.video.nextSlideAtEnd===true) newSlide.bg.video.loop = false;
		}

		newSlide.thumb = _d(obj.thumb,{
			customThumbSrc:"",
			customThumbSrcId:"",
			customAdminThumbSrc:"",
			customAdminThumbSrcId:"",
			dimension:"orig"
			/*fromStream:true*/
		});
		newSlide.info = _d(obj.info,{
			params:[{v:"",l:10},{v:"",l:10},{v:"",l:10},{v:"",l:10},{v:"",l:10},{v:"",l:10},{v:"",l:10},{v:"",l:10},{v:"",l:10},{v:"",l:10}],
			description:""
		});
		newSlide.attributes = _d(obj.attributes,{
			title:"",
			titleOption:"media_library",
			class:"",
			data:"",
			id:"",
			attr:"",
			alt:"",
			altOption:"media_library"
		});
		newSlide.publish = _d(obj.publish,{
			from:"",
			to:"",
			state:"published"
		});
		newSlide.timeline = _d(obj.timeline,{
			stopOnPurpose:false,
			delay:"Default",
			transition:["fade"],
			slots:[0],
			duration:[1000],
			easeIn:["default"],
			easeOut:["default"],
			rotation:[0],
			loop:{
				set:false,
				repeat:"unlimited",
				start:2500,
				end:4500
			}
		});

		newSlide.timeline.loop = newSlide.timeline.loop === undefined ? {set:false, repeat:"unlimited", start:2500, end:4500} : newSlide.timeline.loop;

		newSlide.visibility = _d(obj.visibility,{
			hideAfterLoop:0,
			hideOnMobile:false,
			hideFromNavigation:false
		});

		newSlide.effects = _d(obj.effects,{
			parallax:"-",
			fade:"default",
			blur:"default",
			grayscale:"default"
		});
		newSlide.panzoom = _d(obj.panzoom,{
			set:false,
			blurStart:0,
			blurEnd:0,
			duration:10000,
			ease:"none",
			fitEnd:100,
			fitStart:100,
			xEnd:0,
			yEnd:0,
			xStart:0,
			yStart:0,
			rotateStart:0,
			rotateEnd:0
		});
		newSlide.seo = _d(obj.seo,{
			set:false,
			link:"",
			linkHelp:"auto",
			slideLink:"nothing",
			target:"_self",
			z:"front",
			type:"regular"

		});
		newSlide.nav = _d(obj.nav,{
						arrows:{presets:{}},
						thumbs:{presets:{}},
						tabs:{presets:{}},
						bullets:{presets:{}}
					});

		/* Store view visibility */

		newSlide.store_permissions = _d(obj.store_permissions, false);
		newSlide.allow_stores = {};
		if (obj.allow_stores) {
			for (var i in obj.allow_stores) if (obj.allow_stores.hasOwnProperty(i)) {
				newSlide.allow_stores[i] = obj.allow_stores[i];
			}
		}

		// backwards compatibility
		if (obj.store_id) {
			var storeIds = obj.store_id.split(',');
			newSlide.store_permissions = storeIds.indexOf('0') == -1;
			for (var i in storeIds) if (storeIds.hasOwnProperty(i) && storeIds[i] !== '0') {
				newSlide.allow_stores['store' + storeIds[i]] = true;
			}
		}

		return newSlide;
	};

	// SIMPLIFY SINGLE Slide OBJECT STRUCTURE
	RVS.F.simplifySlide = function(_) {
		if (_.type==="zone")
			return RVS.F.safeExtend(true,{},_);
		else
			return RVS.F.safeExtend(true,{}, RVS.F.simplifyObject(RVS.F.addSlideObj(undefined,true),RVS.F.safeExtend(true,{},_)));
	};
	// SIMPLIFY ALL Slide STRUCTURE
	RVS.F.simplifyAllSlide = function(_) {
		window.__Slides = {};
		for (var i in RVS.SLIDER.slideIDs) {
			if(!RVS.SLIDER.slideIDs.hasOwnProperty(i)) continue;
			if (!jQuery.isNumeric(RVS.SLIDER.slideIDs[i]) && RVS.SLIDER.slideIDs[i].indexOf("static")>=0) {
			} else {
				window.__Slides[RVS.SLIDER.slideIDs[i]] = RVS.F.simplifySlide(RVS.SLIDER[RVS.SLIDER.slideIDs[i]].slide);
			}
		}
	};

	// BUILD THE FULL LAYER STRUCTURE OF SIMPLIFIED STRUCTURES
	RVS.F.expandSlide = function(slide) {
		return RVS.F.safeExtend(true,RVS.F.addSlideObj(), slide);
	};



	/***********************************
			INTERNAL FUNCTIONS
	************************************/

	/*
	SET VALUE TO A OR B DEPENDING IF VALUE A EXISTS AND NOT UNDEFINED OR NULL
	*/
	function _d(a,b) {
		if (a===undefined || a===null)
			return b;
		else
			return a;
	}


	function _truefalse(v) {
		if (v==="false" || v===false || v==="off" || v===undefined || v===0 || v===-1)
			v=false;
		else
			v=true;
		return v;
	}

})();
