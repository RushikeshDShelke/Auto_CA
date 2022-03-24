/*!
 * REVOLUTION 6.0.0 EDITOR TIMELINE JS
 * @version: 1.0 (01.07.2019)
 * @author ThemePunch
*/


// "use strict";
/**********************************
	-	REVBUILDER timeline	-
********************************/
(function() {



	var splitTypes = ["chars","words","lines"],animatedLayers = [],idleMode,tlr,mainTimeLine,frameInfo,slideMaxTime,frameMagnify,cLayer,PN,keyframecache;

	RVS.F.initTimeLineModules = function() {
		initLocalListeners();
		RVS.TL.c.layertime = jQuery('#layer_simulator_time');
		RVS.TL.c.layerprogress = jQuery('#layer_animation_progressarrow');
	};



	RVS.F.animateSlide = function(nextsh,actsh,comingtransition,MS) {
		return interSlideAnimation(nextsh,actsh,comingtransition,MS);
	};


	/*
	TIME LINE FUNCTIONS
	*/
	RVS.F.buildMainTimeLine = function(obj) {
		RVS.TL[RVS.S.slideId].main = new tpGS.TimelineMax({paused:true});

		RVS.F.buildSlideAnimation({animation:RVS.SLIDER[RVS.S.slideId].slide.timeline.transition[RVS.S.slideTrans]});
		RVS.TL[RVS.S.slideId].main.add(RVS.TL[RVS.S.slideId].slide,0);
		RVS.TL[RVS.S.slideId].main.add("end",RVS.F.getSlideLength()/100);
		window.tpfake = 1;
		RVS.TL[RVS.S.slideId].main.add(new tpGS.gsap.set(window,{tpfake:0}),"end");

		if (RVS.SLIDER[RVS.S.slideId].slide.panzoom.set && RVS.TL[RVS.S.slideId].panzoom!==undefined) RVS.TL[RVS.S.slideId].main.add(RVS.TL[RVS.S.slideId].panzoom,0);

		if (obj && (obj.time || obj.progress)) RVS.F.updateTimeLine({timeline:"main",state:"time",time:obj.tim});


		RVS.TL[RVS.S.slideId].main.eventCallback("onUpdate", function() {
			RVS.F.updateCurTime({pos:true, cont:true, left:(this._time*100),refreshMainTimeLine:false, caller:"buildMainTimeLine"});
		});

		RVS.TL[RVS.S.slideId].main.eventCallback('onComplete',function() {
			if (RVS.TL.timelineStartedFromPlayStop) {
				RVS.TL.timelineStartedFromPlayStop = false;
				RVS.TL.cache.main = 0;
				RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
			}
		});




	};

	RVS.F.updateFramesZIndexes = function(_) {
		var z = 1000;
		if (RVS.L[_.layerid].timeline.frameOrder===undefined) RVS.F.getFrameOrder({layerid:_.layerid});
		for (var oi in RVS.L[_.layerid].timeline.frameOrder) {
			if(!RVS.L[_.layerid].timeline.frameOrder.hasOwnProperty(oi)) continue;
			if (RVS.L[_.layerid].timeline.frameOrder[oi].id ==="frame_0") continue;
			var el = document.getElementById(RVS.S.slideId+'_'+_.layerid+'_'+RVS.L[_.layerid].timeline.frameOrder[oi].id);
			if (el) tpGS.gsap.set(el,{zIndex:z});
			z--;
		}
	};

	/*
	FRAME HANDLINGS
	*/
	RVS.F.addFrame = function(obj) {

		var fff = obj.frame==="frame_1" ? '<ffbefore data-frame="frame_0"></ffbefore><ffafter data-frame="frame_1"></ffafter>' : '',
			_ = {wrap:obj.container,bg:obj.container.find('framebg').first(),frame: jQuery('<framewrap id="'+RVS.S.slideId+'_'+obj.layerid+'_'+obj.frame+'" data-layertype="'+obj.layertype+'" data-layerid="'+obj.layerid+'" data-frame="'+obj.frame+'" class="frame_'+obj.frame+'"><frame><framedelay>2500</framedelay><startloop><i class="material-icons">chevron_right</i></startloop><endloop><i class="material-icons">chevron_left</i></endloop>'+fff+'<marker></marker><frameinfo></frameinfo></frame></framewrap>')};

		//CHECK WHERE TO ADD THE FRAME
		obj.container.append(_.frame);

		_.info = _.frame.find('frameinfo').first();
		_.framedelay = _.frame.find('framedelay').first();
		_.sloop = _.frame.find('startloop');
		_.eloop = _.frame.find('endloop');
		if (obj.resize!==undefined) {
			_.frame.resizable({
				handles:obj.resize,
				minWidth:5,
				start:obj.start,
				stop:obj.stopresize,
				resize:obj.onresize
			});
		}
		if (obj.ondrag!==undefined) {
			_.frame.draggable({
				axis:"x",
				delay:200,
				start:obj.start,
				stop:obj.stopdrag,
				drag:obj.ondrag
			});

			_.bg.draggable({
				axis:"x",
				delay:200,
				start:obj.start,
				stop:obj.stopdrag,
				drag:obj.ondrag
			});
		}
		return _;
	};
	/*
	BUILD 1x MAIN SLIDE FRAME (USED FOR ALL SLIDES, NO DIFFERENCES BETWEEN SLIDES, ONLY 1 REFERENCE)
	*/
	RVS.F.buildSlideFrames = function() {
		RVS.TL.fref = RVS.TL.fref===undefined ?
				RVS.F.addFrame({
					container:jQuery('#slide_frame_container .frameswrap'),
					frame:"0",
					resize:"e",
					layerid:"",
					start:function(event,ui) {
						RVS.F.selectFirstActiveTransition();
						mainTimeLine = RVS.TL[RVS.S.slideId] && RVS.TL[RVS.S.slideId].main ? RVS.TL[RVS.S.slideId].main.time() : 0;
						mainTimeLine = mainTimeLine<0.0015 ? "idle" : mainTimeLine;
						frameInfo = ui.element.find('frameinfo');
						slideMaxTime = RVS.F.getSlideLength();
						RVS.TL.inDrag = true;
					},
					onresize:function(event,ui) {
						ui.size.width = ui.size.width<=slideMaxTime ? ui.size.width : slideMaxTime;
						RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.timeline.duration.0",val:ui.size.width*10,ignoreBackup:true});
						RVS.F.buildMainTimeLine({time:mainTimeLine});
						frameInfo[0].innerHTML = ui.size.width*10;
					},
					stopresize:function(event,ui) {
						//CREATE BACKUP
						RVS.F.backup({path:RVS.S.slideId+".slide.timeline.duration.0", lastkey:"speed", val:ui.size.width*10, old:ui.originalSize.width*10});
						RVS.F.selectFirstActiveTransition();
						RVS.F.buildMainTimeLine({time:mainTimeLine});
						RVS.F.timelineDragsStop();

					}
				}) : RVS.TL.fref;
		RVS.F.updateSlideFrames();
	};

	/*
	UPDATE THE SLIDE FRAME SIZES
	*/
	RVS.F.updateSlideFrames = function(obj) {
		obj = obj===undefined ? {} : obj;
		obj.slidedelay = obj.slidedelay===undefined ? RVS.F.getSlideLength() : obj.slidedelay;
		var MS = RVS.F.getSlideAnimParams("duration");
		MS = MS==="default" || MS==="Default" || MS==="Random" ? "Default" : parseInt(MS,0) / 10;
		obj.animspeed = obj.animspeed===undefined ? MS : obj.animspeed;
		tpGS.gsap.set(RVS.TL.fref.wrap,{width: obj.slidedelay});
		tpGS.gsap.set(RVS.TL.fref.frame,{width: obj.animspeed});
		RVS.TL.fref.info[0].innerHTML = obj.animspeed*10;
	};


	/*
	ADD ALL FRAMES IN ONE GO FROM ONE LAYER
	*/
	RVS.F.addLayerFrames = function(layer,jlayer) {
		RVS.TL[RVS.S.slideId].layers = RVS.TL[RVS.S.slideId].layers===undefined ? {} : RVS.TL[RVS.S.slideId].layers;
		RVS.TL[RVS.S.slideId].layers[layer.uid] = RVS.TL[RVS.S.slideId].layers[layer.uid] ===undefined ? {} : RVS.TL[RVS.S.slideId].layers[layer.uid];
		var slideLength = RVS.F.getSlideLength()*10;
		if (layer.timeline.frameOrder===undefined) RVS.F.getFrameOrder({layerid:layer.uid});
		for (var oi in layer.timeline.frameOrder) {
			if(!layer.timeline.frameOrder.hasOwnProperty(oi)) continue;
			var findex = layer.timeline.frameOrder[oi].id;
			if (findex ==="frame_0") continue;
			layer.timeline.frames.frame_999.timeline.start = layer.timeline.frames.frame_999.timeline.start===0 || layer.timeline.frames.frame_999.timeline.start> slideLength ? slideLength : layer.timeline.frames.frame_999.timeline.start;
			RVS.F.addLayerFrameOnDemand(layer,jlayer,findex);
		}
		RVS.F.updateFramesZIndexes({layerid:layer.uid});
	};

	RVS.F.addLayerFrameOnDemand = function(layer,jlayer,frame) {
		RVS.F.addLayerFrame({	frame:layer.timeline.frames[frame],
								frameindex:frame,
								layerid:layer.uid,
								layertype:layer.type,
								framecontainer:RVS.TL[RVS.S.slideId].layers[layer.uid],
								wrap:jlayer.find('.frameswrap').first()
							});
		RVS.F.updateLayerFrame({layerid:layer.uid, frame:frame});
	};

	/*
	ADD LAYER FRAME AND ITS LISTENERS
	*/
	RVS.F.getLayerAliasAndFrame = function(_) {
		var r = _.frame.replace("_"," ").replace("f","F");
		r = RVS.F.sanitize_input(RVS.L[_.layerid].alias)+" "+r;
		return r;
	};

	/*
	UPDATE THE CHILDREN POSITIONS ON THE TIMELINE
	*/
	RVS.F.setChildrenTimelines = function(_) {
		if (_.childLayers===undefined) return;

		for (var li in _.childLayers) {
			if(!_.childLayers.hasOwnProperty(li)) continue;
			var cl = _.childLayers[li];
			for (var fi in cl.frames) {
				if(!cl.frames.hasOwnProperty(fi)) continue;
				if (fi==="frame_0") continue;
				var limits = RVS.F.getPrevNextFrame({layerid:li, frame:fi}),
					nstart = cl.frames[fi]/10 -_.difference;

				if (limits.prev.end/10>=nstart)
					nstart = limits.prev.end/10;
				else
				if (limits.next.start/10<=nstart+limits.cur.framelength/10)
					nstart = limits.next.start/10 - limits.cur.framelength/10;

				if (nstart>slideMaxTime)
					nstart = slideMaxTime;

				if (cl.endWithSlide===undefined)
					RVS.SLIDER[RVS.S.slideId].layers[li].timeline.frames[fi].timeline.start = nstart*10;
				else
				if (cl.endWithSlide)
					RVS.SLIDER[RVS.S.slideId].layers[li].timeline.frames[fi].timeline.start = nstart*10;
				else
				if (cl.frames[fi]>slideMaxTime*10 && fi==="frame_999" && !cl.endWithSlide) {
					RVS.SLIDER[RVS.S.slideId].layers[li].timeline.frames[fi].timeline.start = Math.max(limits.prev.end,slideMaxTime*10);

				}
				RVS.F.updateLayerFrame({layerid:li, frame:fi,maxtime:slideMaxTime});
			}
		}
	};

	/*
	UPDATE THE CHILDREN POSITIONS ON THE TIMELINE
	*/
	RVS.F.moveChildrenTimelines = function(_) {
		if (_.childLayers===undefined) return;
		for (var li in _.childLayers) {
			if(!_.childLayers.hasOwnProperty(li)) continue;
			if (frameMagnify == 1 && li!==cLayer.layerid) continue;
			var cl = _.childLayers[li],
				flen = cl.forder.length-1;
			for (var i in cl.forder) {
				if(!cl.forder.hasOwnProperty(i)) continue;
				var fi = _.order===-1 ? cl.forder[flen-i] : cl.forder[i];
				if (fi==="frame_0") continue;
				var limits = RVS.F.getPrevNextFrame({layerid:li, frame:fi}),
					nstart = cl.frames[fi]/10 -_.difference;

				if (limits.prev.frameid===cLayer.frame && _.thend && limits.prev) nstart = nstart < _.thend ? _.thend : nstart;
				if (0>nstart) nstart = 0;
				if (limits.next.start/10<nstart+limits.cur.framelength/10) nstart = limits.next.start/10 - limits.cur.framelength/10;
				if (nstart>slideMaxTime) nstart = slideMaxTime;
				if (cl.endWithSlide===undefined || cl.endWithSlide) RVS.SLIDER[RVS.S.slideId].layers[li].timeline.frames[fi].timeline.start = nstart*10;
				if (cl.frames[fi]>slideMaxTime*10 && fi==="frame_999" && !cl.endWithSlide) RVS.SLIDER[RVS.S.slideId].layers[li].timeline.frames[fi].timeline.start = Math.max(limits.prev.end,slideMaxTime*10);
				RVS.F.updateLayerFrame({layerid:li, frame:fi,maxtime:slideMaxTime});
			}
		}
	};


	/*
	BACKUP THE CHILDREN CHANGES ON TIMELINE. ONLY CALL FROM BACKUPGROUP !!
	*/
	RVS.F.backupChildren = function(_) {
		if (_.childLayers===undefined) return;
		for (var li in _.childLayers) {
			if(!_.childLayers.hasOwnProperty(li)) continue;
			var cl = _.childLayers[li];
			for (var fi in cl.frames) {
				if(!cl.frames.hasOwnProperty(fi)) continue;
				RVS.F.backup({	path:RVS.S.slideId+".layers."+li+".timeline.frames."+fi+".timeline.start",
								lastkey:"start",
								val:RVS.SLIDER[RVS.S.slideId].layers[li].timeline.frames[fi].timeline.start,
								old:cl.frames[fi]});
			}
		}
	};

	RVS.F.getFirstFrame = function(_) {
		if (RVS.L[_.layerid].timeline.frameOrder===undefined) RVS.F.getFrameOrder({layerid:_.layerid});
		if (RVS.L[_.layerid].timeline.frameOrder[0].id==="frame_0")
			return RVS.L[_.layerid].timeline.frameOrder[1].id;
		else
			return RVS.L[_.layerid].timeline.frameOrder[0].id;
	};

	// ADD ONE SINGLE LAYER FRAME TO THE TIMELINE
	RVS.F.addLayerFrame = function(_) {
		_.framecontainer[_.frameindex] = RVS.F.addFrame({
			container:_.wrap,
			frame:_.frameindex,
			layerid:_.layerid,
			layertype:_.layertype,
			resize:"w,e",

			start:function(event,ui) {
				RVS.TL.inDrag = true;
				RVS.TL.tS.addClass("frame_in_drag");

				RVS.TL.timeBeforeFrameChange = RVS.TL[RVS.S.slideId].main.time();
				cLayer = ui.element===undefined ? { licontainer:document.getElementById('tllayerlist_element_'+RVS.S.slideId+"_"+ui.helper[0].dataset.layerid) , layerid:ui.helper[0].dataset.layerid, frame:ui.helper[0].dataset.frame, type:ui.helper[0].dataset.layertype, bg:ui.helper[0].dataset.bg}
								: { layerid:ui.element[0].dataset.layerid, frame:ui.element[0].dataset.frame, type:ui.element[0].dataset.layertype, bg:ui.element[0].dataset.bg};
				if (cLayer.bg!=="true") {
					cLayer.frameName = RVS.F.getLayerAliasAndFrame(cLayer);
					PN =  RVS.F.getPrevNextFrame(cLayer);
					cLayer.path = RVS.F.getLayerObjPath(cLayer);
				} else {
					cLayer.layerwidth = ui.helper.width();
					cLayer.frame = "All Frames";
					PN =  RVS.F.getPrevNextFrame({layerid:cLayer.layerid, frame:RVS.F.getFirstFrame({layerid:cLayer.layerid})});
					cLayer.frameName = RVS.F.getLayerAliasAndFrame(cLayer);
				}



				slideMaxTime = RVS.F.getSlideLength();

				// IF BG DRAGGED, OR DRAGGED && IT IS A GROUPPING ELEMENT
				if ((cLayer.bg && event.type=="dragstart" && jQuery.inArray(cLayer.type,["column","row","group"])>=0) || (event.type=="dragstart" && cLayer.frame==RVS.F.getFirstFrame({layerid:cLayer.layerid}) && jQuery.inArray(cLayer.type,["column","row","group"])>=0))
					cLayer.childLayers = RVS.F.getLayerChildren({layerid:cLayer.layerid});


				//EXTEND CHILDREN WITH ITS OWN FRAMES IF WE DRAG THE BG
				if (cLayer.bg)
					cLayer.childLayers = RVS.F.getLayerFrames({layerid:cLayer.layerid, extend:cLayer.childLayers});
				else // EXTEND CHILDREN WITH THE FRAMES BEHIND THE CURRENT FRAME ON THE SAME TIMELINE
					cLayer.childLayers = RVS.F.getLayerFrames({layerid:cLayer.layerid, extend:cLayer.childLayers, afterStart:PN.cur.start, include999:true});

				//IF FRAMEMAGNIFY IS ON
				if (frameMagnify == 1 || frameMagnify == 2)
					for (var i in cLayer.childLayers) {
						if(!cLayer.childLayers.hasOwnProperty(i)) continue;
						cLayer.childLayers[i].forder = [];
						for (var fi in cLayer.childLayers[i].frames) {
							if(!cLayer.childLayers[i].frames.hasOwnProperty(fi)) continue;
							cLayer.childLayers[i].forder.push(fi);
						}
					}
				//window.smallestChildLayerStarts = RVS.F.getSmallestFrameInChildren({childLayers:cLayer.childLayers});

				// Here Attach also all Selected Layers Later (Extend the cLayer.childLayers) Object. Everything else will be done automatically

				// UPDATE FRAME TIME
				RVS.F.updateFrameTime({pos:true, cont:true, left:(PN.cur.start-0.310)});

				if (cLayer.frame === "frame_1") {
					jQuery(cLayer.licontainer).addClass("frame_1_indrag");
					for (var i in cLayer.childLayers) {
						if(!cLayer.childLayers.hasOwnProperty(i)) continue;
						cLayer.childLayers[i].hiddenc = document.getElementById('frame_unvisible_start_'+RVS.S.slideId+"_"+i);
					}

				}

			},
			ondrag:function(event,ui) {

				if (ui.position.left>window.lastCachedUiPosition && (frameMagnify == 1 || frameMagnify == 2)) RVS.F.moveChildrenTimelines({thend:((ui.position.left+cLayer.framelength/10)), order:-1,childLayers:cLayer.childLayers, difference: (ui.originalPosition.left - ui.position.left)});
				PN =  RVS.F.getPrevNextFrame(cLayer);

				//CHECK FOR FRAMES
				if (cLayer.bg!=="true") {
					if (cLayer.frame!=="frame_1" && PN.prev.end/10>=ui.position.left) ui.position.left = PN.prev.end/10;
					if (cLayer.frame=="frame_1" && 0>=ui.position.left) ui.position.left = 0;
					if (PN.next.layerid == cLayer.layerid && PN.next.start/10<=ui.position.left+PN.cur.framelength/10) ui.position.left = PN.next.start/10 - PN.cur.framelength/10;
					if (ui.position.left>slideMaxTime) ui.position.left = slideMaxTime;
				} else {//CHECK FOR FULL CONTAINER
					ui.position.left = ui.position.left<PN.prev.end/10 ? PN.prev.end/10 :
									   parseInt(ui.position.left,0) + parseInt(cLayer.layerwidth,0) >= slideMaxTime ? slideMaxTime - cLayer.layerwidth :
									   ui.position.left;
				}

				if (frameMagnify == 1 || frameMagnify == 2)  RVS.F.moveChildrenTimelines({thend:((ui.position.left+cLayer.framelength/10)), childLayers:cLayer.childLayers, difference: (ui.originalPosition.left - ui.position.left)});

				window.lastCachedUiPosition = ui.position.left;

				if (cLayer.bg!=="true") {
					RVS.F.updateSliderObj({path:cLayer.path+"start",val:Math.round(ui.position.left*10),ignoreBackup:true});
					RVS.F.updateSliderObj({path:cLayer.path+"startRelative",val:(Math.round(ui.position.left*10) - PN.prev.end),ignoreBackup:true});
					cLayer.framelength = PN.cur.framelength;
					RVS.F.updateLayerFrame(cLayer);
				} else {
					RVS.F.updateLayerFrame({layerid:cLayer.layerid, frame:"frame_999"});
				}
				if (jQuery.inArray(parseInt(cLayer.layerid,0),RVS.selLayers)>=0) document.getElementById('layerframestart').value = Math.round(ui.position.left*10);

				//RE RENDER ANIMATION OF INVOLVED LAYERS
				/*if (RVS.TL.timeBeforeFrameChange!==0) {
					RVS.F.renderLayerAnimation({layerid:cLayer.layerid, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
					for (var i in cLayer.childLayers)
						RVS.F.renderLayerAnimation({layerid:i, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
				}*/

				// UPDATE FRAME TIME
				RVS.F.updateFrameTime({pos:true, cont:true, left:ui.position.left-0.310});
				for (var i in cLayer.childLayers) {
					if(!cLayer.childLayers.hasOwnProperty(i)) continue;
					if (i!==cLayer.layerid) tpGS.gsap.set(cLayer.childLayers[i].hiddenc,{width:(ui.position.left+20)});
				}
				PN =  RVS.F.getPrevNextFrame(cLayer);
			},

			onresize:function(event,ui) {
				if (cLayer.frame!=="frame_1" && PN.prev.end/10>ui.position.left) {
					ui.position.left = PN.prev.end/10;
					ui.size.width = ((PN.cur.end - PN.prev.end) / 10);
				} else
				if (cLayer.frame=="frame_1" && 0>ui.position.left) {
					ui.position.left = 0;
					ui.size.width = (PN.cur.end) / 10;
				} else
				if (PN.next.start/10<=ui.position.left+ui.size.width) {
					ui.size.width = (( PN.next.start - PN.cur.start) / 10);
				}

				if (ui.position.left>slideMaxTime)
					ui.position.left = slideMaxTime;

				RVS.F.updateSliderObj({path:cLayer.path+"start",val:Math.round(ui.position.left*10),ignoreBackup:true});
				RVS.F.updateSliderObj({path:cLayer.path+"startRelative",val:(Math.round(ui.position.left*10) - PN.prev.end),ignoreBackup:true});
				RVS.F.updateSliderObj({path:cLayer.path+"speed",val:Math.round((ui.size.width*10) - PN.cur.splitDelay),ignoreBackup:true});
				RVS.F.updateLayerFrame(cLayer);
				if (jQuery.inArray(parseInt(cLayer.layerid,0),RVS.selLayers)>=0) {
					document.getElementById('layerframespeed').value = Math.round((ui.size.width*10) - PN.cur.splitDelay);
					document.getElementById('layerframestart').value = Math.round(ui.position.left*10);
					document.getElementById('layerframespeed_sub').innerHTML = "("+Math.round(ui.size.width*10)+")";
				}
				//RE RENDER ANIMATION OF INVOLVED LAYERS
				/*if (RVS.TL.timeBeforeFrameChange!==0) {
					RVS.F.renderLayerAnimation({layerid:cLayer.layerid, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
					for (var i in cLayer.childLayers)
						RVS.F.renderLayerAnimation({layerid:i, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
				}*/
			},

			stopdrag:function(event,ui) {
				jQuery(cLayer.licontainer).removeClass("frame_1_indrag");
				RVS.F.timelineDragsStop();
				//BACKUP VALUES
				RVS.F.openBackupGroup({id:"frame",txt:cLayer.frameName+" Start",icon:"access_time"});

				//UPDATE RELATIVE TIMES AND CHILDREN RELATIVE TIMES->
				RVS.F.updateAllstartRelatives();

				RVS.F.backup({path:cLayer.path+"start", lastkey:"start", val:Math.round(ui.position.left*10), old:Math.round(ui.originalPosition.left*10)});
				RVS.F.backupChildren({childLayers:cLayer.childLayers});
				RVS.F.closeBackupGroup({id:"frame"});
				RVS.F.renderLayerAnimation({layerid:cLayer.layerid, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
				for (var i in cLayer.childLayers) {
					if(!cLayer.childLayers.hasOwnProperty(i)) continue;
					RVS.F.renderLayerAnimation({layerid:i, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
				}
				RVS.TL.tS.removeClass("frame_in_drag");
			},

			stopresize:function(event,ui) {
				RVS.F.timelineDragsStop();
				//BACKUP VALUES
				RVS.F.openBackupGroup({id:"frame",txt:cLayer.frameName+" Speed",icon:"slow_motion_video"});
				//UPDATE RELATIVE TIMES AND CHILDREN RELATIVE TIMES->
				RVS.F.updateAllstartRelatives();
				RVS.F.backup({path:cLayer.path+"start", lastkey:"start", val:Math.round(ui.position.left*10), old:Math.round(ui.originalPosition.left*10)});
				RVS.F.backup({path:cLayer.path+"speed", lastkey:"speed", val:Math.round((ui.size.width*10) - PN.cur.splitDelay), old:Math.round((ui.originalSize.width*10) - PN.cur.splitDelay)});
				RVS.F.closeBackupGroup({id:"frame"});
				ui.element.css({maxWidth:"none"});
				RVS.F.renderLayerAnimation({layerid:cLayer.layerid, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
				for (var i in cLayer.childLayers) {
					if(!cLayer.childLayers.hasOwnProperty(i)) continue;
					RVS.F.renderLayerAnimation({layerid:i, timeline:"full",time:RVS.TL.timeBeforeFrameChange});
				}
				RVS.TL.tS.removeClass("frame_in_drag");
			}
		});
	};

	RVS.F.updateAllstartRelatives = function() {
		for (var li in RVS.L) {
			if(!RVS.L.hasOwnProperty(li)) continue;
			if (li>=0 && li<=9999)
				for (var oi in RVS.L[li].timeline.frameOrder) {
					if(!RVS.L[li].timeline.frameOrder.hasOwnProperty(oi)) continue;
					var fi = RVS.L[li].timeline.frameOrder[oi].id;
					if (fi ==="frame_0") continue;
					var	a = RVS.F.getPrevNextFrame({layerid:RVS.L[li].uid, frame:fi});
					RVS.F.updateSliderObj({path:RVS.F.getLayerObjPath({layerid:RVS.L[li].uid, frame:fi})+"startRelative",val:(a.cur.start - a.prev.end)});
				}
		}
	};

	RVS.F.getLayerObjPath = function(_) {
		return RVS.S.slideId+".layers."+_.layerid+".timeline.frames."+_.frame+".timeline.";
	};

	/*
	GET PREVIOUS AND NEXT FRAME IN TIME
	*/
	RVS.F.getPrevNextFrame = function(_) {

		var frame = RVS.L[_.layerid].timeline.frames[_.frame].timeline,
			framesd = RVS.F.getSplitDelay({layerid:_.layerid, frame:_.frame}),
			frameend = RVS.F.addT([frame.start,frame.speed,framesd]),
			firstframe = RVS.F.getFirstFrame({layerid:_.layerid}),
			pn = {	cur:{start:frame.start, end:frameend, splitDelay:framesd, framelength:(frameend-frame.start)},
					prev:{start:-1,end:0,frame:{}},
					next:{start:9999999, end:9999999, frame:{}}
				};

			//emptyspace calculation for Stucked Frame Movents ?!

		for (var fi in RVS.L[_.layerid].timeline.frames) {
			if(!RVS.L[_.layerid].timeline.frames.hasOwnProperty(fi)) continue;
			if (fi==="frame_0") continue;
			if (fi!==_.frame) {
				var c = RVS.L[_.layerid].timeline.frames[fi].timeline;
				if (c.start<frame.start && c.start>pn.prev.start)
					pn.prev = {start:c.start, end:RVS.F.addT([c.start,c.speed,RVS.F.getSplitDelay({layerid:_.layerid, frame:fi})]), frame:c,layerid:_.layerid, frameid:fi};
				if (c.start>frame.start && c.start<pn.next.start)
					pn.next = {start:c.start, end:RVS.F.addT([c.start,c.speed,RVS.F.getSplitDelay({layerid:_.layerid, frame:fi})]), frame:c, layerid:_.layerid, frameid:fi};
			}
		}

		if (_.frame==firstframe && RVS.L[_.layerid].group.puid!==-1 && jQuery.inArray(RVS.L[_.layerid].group.puid,["top","bottom","middle"])==-1) {
			var gfirstframe = RVS.F.getFirstFrame({layerid:RVS.L[_.layerid].group.puid}),
				gframe = RVS.L[RVS.L[_.layerid].group.puid].timeline.frames[gfirstframe].timeline;
			pn.prev.end = gframe.start;
			/* var _framesd = 	RVS.F.getSplitDelay({layerid:RVS.L[_.layerid].group.puid, frame:gfirstframe}), */
			var _frameend = RVS.F.addT([gframe.start,gframe.speed,framesd]);
			pn.prev.framelength = _frameend - gframe.start;
			pn.prev.realEnd = _frameend;
		}


		return pn;
	};

	/*
	UPDATE RELATIVE TIME ON FRAME
	*/
	RVS.F.setStartRelative = function(_) {
		if (RVS.TL[RVS.S.slideId].layers===undefined || RVS.TL[RVS.S.slideId].layers[_.layerid]===undefined) return;

	};
	/*
	UPDATE THE REAL SPEED IN FRAME SPEED SUB FIELD
	*/
	RVS.F.updateFrameRealSpeed = function() {
		document.getElementById('layerframespeed_sub').innerHTML = "(" + RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].timeline.frameLength + ")";
	};

	/*
	GET LENGTH OF SPLIT TIME TO EXTEND FRAME LENGTHS
	*/
	RVS.F.getSplitDelay = function(_) {
		if (RVS.H[_.layerid]!==undefined && RVS.H[_.layerid].splitText!==undefined) {
			var frame = RVS.L[_.layerid].timeline.frames[_.frame],
				split = frame.chars.use ? "chars" : frame.words.use ? "words" : frame.lines.use ? "lines" : undefined;
			return split!==undefined ? RVS.H[_.layerid].splitText[split].length * (frame[split].delay===undefined ? 0 : frame[split].delay*10) : 0;

		} else return 0;
	};

	/*
	UPDAE ALL LAYER FRAMES
	*/
	RVS.F.updateAllLayerFrames = function(_) {

		_ = _===undefined ? {} : _;
		for (var li in RVS.L) {
			if(!RVS.L.hasOwnProperty(li)) continue;
			if (li!=="top" && li!=="bottom" && li!=="middle") {
				jQuery('#tllayerlist_element_'+RVS.S.slideId+'_'+RVS.L[li].uid+" .layerlist_element_alias").first().text(RVS.L[li].alias);
				for (var oi in RVS.L[li].timeline.frameOrder) {
					if(!RVS.L[li].timeline.frameOrder.hasOwnProperty(oi)) continue;
					var fi = RVS.L[li].timeline.frameOrder[oi].id;
					if (fi ==="frame_0") continue;
					if (_.frame===undefined || fi===_.frame) {
						RVS.F.updateLayerFrame({layerid:li, frame:fi});
					}
				}
			}
		}
	};

	RVS.F.updateLayerFrames = function(_) {
		for (var fi in RVS.L[_.layerid].timeline.frames) {
			if(!RVS.L[_.layerid].timeline.frames.hasOwnProperty(fi)) continue;
			if ((_.frame===undefined || fi===_.frame) && fi!=="frame_0") {
				RVS.F.updateLayerFrame({layerid:_.layerid, frame:fi});
			}
		}
	};



	/*
	UPDATE LAYER FRAME SIZE, POSITION
	*/
	RVS.F.updateLayerFrame = function(_) {
		if (RVS.TL[RVS.S.slideId].layers===undefined || RVS.TL[RVS.S.slideId].layers[_.layerid]===undefined) return;
		var ref = RVS.TL[RVS.S.slideId].layers[_.layerid][_.frame],
			firstframe = RVS.F.getFirstFrame({layerid:_.layerid}),
			tl = RVS.L[_.layerid].timeline.frames[_.frame].timeline,
			/* f0 = RVS.L[_.layerid].timeline.frames[firstframe].timeline, */
			f999 = RVS.L[_.layerid].timeline.frames.frame_999.timeline,
			framelength = _.framelength==undefined ? RVS.F.addT([tl.speed,RVS.F.getSplitDelay({layerid:_.layerid, frame:_.frame})]) : _.framelength;

		//tpGS.gsap.set(ref.bg,{left:f0.start/10, width:(f999.start - f0.start)/10+"px"});
		tpGS.gsap.set(ref.frame,{left:tl.start/10+"px", width:framelength/10});
		ref.info[0].innerHTML = framelength;


		tl.frameLength = framelength;

		if (_.frame==="frame_999") {
			ref.endframemarker = ref.endframemarker===undefined || ref.endframemarker.length==0 ? jQuery('#slideendmarker_'+RVS.S.slideId+'_'+_.layerid) : ref.endframemarker;
			_.maxtime = _.maxtime === undefined ? RVS.F.getSlideLength() : _.maxtime;

			if (tl.start/10>=_.maxtime) {
				ref.endframemarker.addClass("endswithslide");
				f999.endWithSlide = true;
			}
			else {
				ref.endframemarker.removeClass("endswithslide");
				f999.endWithSlide = false;
			}
		}

		tl.actionTriggered = RVS.F.layerFrameTriggeredBy({layerid:_.layerid,frame:_.frame}).uid!=="" && RVS.F.layerFrameTriggered({layerid:_.layerid, frame:_.frame});
		ref.framedelay[0].innerHTML =  tl.actionTriggered ? "a" : tl.endWithSlide===true ? RVS_LANG.framewait : tl.start;
		if (RVS.L[_.layerid].timeline!=undefined) {
			ref.sloop[0].style.display= RVS.L[_.layerid].timeline.tloop.use && RVS.L[_.layerid].timeline.tloop.from===_.frame ? "block" : "none";
			ref.eloop[0].style.display= RVS.L[_.layerid].timeline.tloop.use && RVS.L[_.layerid].timeline.tloop.to===_.frame ? "block" : "none";
		}
 		ref.framedelay[0].className = tl.endWithSlide===true ? "coloredbg" : tl.actionTriggered && _.frame ===firstframe ? "coloredbgover" : tl.actionTriggered ? "coloredbg" : "";
	};


	RVS.F.updateAllLayerToIDLE = function() {
		for (var li in RVS.L) {
			if(!RVS.L.hasOwnProperty(li)) continue;
			if (RVS.H[li]!==undefined && RVS.H[li].timeline!==undefined)	RVS.H[li].timeline.pause("frame_IDLE");
		}

	};



	/*
	UPDATE ANY TIMELINE
	*/
	RVS.F.updateTimeLine = function(obj) {

		if (obj.force && RVS.TL[RVS.S.slideId]!==undefined && RVS.TL[RVS.S.slideId].main===undefined) RVS.F.buildMainTimeLine();
		if (RVS.TL[RVS.S.slideId]!==undefined && RVS.TL[RVS.S.slideId][obj.timeline]!==undefined) {

			if (obj.timeline==="panzoom") {
				RVS.TL[RVS.S.slideId].slide.progress(1);
				if (RVS.TL[RVS.S.slideId].main) RVS.TL[RVS.S.slideId].main.progress(0);
			}
			if (obj.forceFullLayerRender || (idleMode===true && obj.timeline==="main")) RVS.F.buildFullLayerAnimation("atstart");

			switch (obj.state) {
				case "play":
					if (obj.timeline==="main") idleMode = false;
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].slide) RVS.TL[RVS.S.slideId].slide.play();
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].main) RVS.TL[RVS.S.slideId].main.play();
					if (RVS.TL[RVS.S.slideId].panzoom) RVS.TL[RVS.S.slideId].panzoom.play();
					if (obj.timeline==="main") for (var li in RVS.L) if (RVS.H[li]!==undefined && RVS.H[li].timeline!==undefined) 	RVS.H[li].timeline.play();
				break;
				case "stop":
				case "pause":
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].slide) RVS.TL[RVS.S.slideId].slide.pause();
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].main) RVS.TL[RVS.S.slideId].main.pause();
					for (var li in RVS.L) {
						if(!RVS.L.hasOwnProperty(li)) continue;
						if (RVS.H[li]!==undefined && RVS.H[li].timeline!==undefined) RVS.H[li].timeline.pause();
					}
					if (RVS.TL[RVS.S.slideId].panzoom) RVS.TL[RVS.S.slideId].panzoom.pause();

				break;
				case "rewind":
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].slide) RVS.TL[RVS.S.slideId].slide.time(0);
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].main) RVS.TL[RVS.S.slideId].main.time(0);
					if (obj.timeline==="main") for (var li in RVS.L) if (RVS.L.hasOwnProperty(li)) if (RVS.H[li]!==undefined && RVS.H[li].timeline!==undefined) RVS.H[li].timeline.time(0);
					if (RVS.TL[RVS.S.slideId].panzoom) RVS.TL[RVS.S.slideId].panzoom.time(0);
				break;

				case "time":
					if (obj.timeline==="main") idleMode = obj.time===0;
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].slide) RVS.TL[RVS.S.slideId].slide.time(obj.time);
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].main) RVS.TL[RVS.S.slideId].main.time(obj.time);
					if (RVS.TL[RVS.S.slideId].panzoom)
						if (obj.time===undefined)
							RVS.TL[RVS.S.slideId].panzoom.progress(0);
						else
							RVS.TL[RVS.S.slideId].panzoom.time(obj.time);
					obj.time = obj.time===0 ? "frame_IDLE" : obj.time;
					if (obj.timeline==="main") for (var li in RVS.L) if (RVS.H[li]!==undefined && RVS.H[li].timeline!==undefined) RVS.H[li].timeline.time(obj.time);
				break;
				case "progress":
					if (obj.timeline==="main") idleMode = obj.prgs===0;
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].slide) RVS.TL[RVS.S.slideId].slide.progress(obj.prgs);
					if (obj.timeline==="main" && RVS.TL[RVS.S.slideId].main) RVS.TL[RVS.S.slideId].main.progress(obj.prgs);
					if (obj.timeline==="main") for (var li in RVS.L) if (RVS.L.hasOwnProperty(li)) if (RVS.H[li]!==undefined && RVS.H[li].timeline!==undefined) RVS.H[li].timeline.progress(obj.prgs);
					if (RVS.TL[RVS.S.slideId].panzoom) RVS.TL[RVS.S.slideId].panzoom.progress(obj.prgs);
				break;
				case "getprogress":
					return RVS.TL[RVS.S.slideId][obj.timeline].progress();
				// break;
				case "getstate":
					return RVS.TL[RVS.S.slideId][obj.timeline].isActive();
				// break;
				case "idle":
					RVS.TL.cache = {};
					if (RVS.TL[RVS.S.slideId].main)  RVS.TL.cache.main = RVS.TL[RVS.S.slideId].main.time();
					if (RVS.TL[RVS.S.slideId].panzoom) RVS.TL[RVS.S.slideId].panzoom.progress(0).pause();
					if (RVS.TL[RVS.S.slideId].main) 	RVS.TL[RVS.S.slideId].main.progress(0).pause();
					if (RVS.TL[RVS.S.slideId].slide) RVS.TL[RVS.S.slideId].slide.progress(1).pause();
					RVS.F.changeSwitchState({el:document.getElementById("timline_process"),state:"play"});
					RVS.TL.timelineStartedFromPlayStop=false;
					for (var li in RVS.L) {
						if(!RVS.L.hasOwnProperty(li)) continue;
						if (RVS.H[li]!==undefined && RVS.H[li].timeline!==undefined) RVS.H[li].timeline.pause("frame_IDLE");
					}
					idleMode = true;
				break;
			}

			if (obj.time===0 || obj.time===undefined) tpGS.gsap.set(jQuery("rs-sbg-wrap.slotwrapper_cur"),{autoAlpha:1});


			if (RVS.TL[RVS.S.slideId].main)  RVS.TL.cache.main = RVS.TL[RVS.S.slideId].main.time();
			RVS.TL.requestedTime = obj.time===undefined ? RVS.TL[RVS.S.slideId].main.time() : obj.time;

			if (obj.updateCurTime) RVS.F.updateCurTime({pos:true, cont:true, force:true, left:RVS.TL.cache.main*100,refreshMainTimeLine:false});
		} else {
			return false;
		}
	};

	// GET END TIME OF A FRAME
	RVS.F.getTimeAtSelectedFrameEnd = function() {
		var _ = 0;
		try{ _=(RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].timeline.start/10) + (RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].timeline.frameLength/10);}
		catch(e) {}
		return _;
	};

	// GET MIDDLE TIME OF A FRAME
	RVS.F.getTimeAtSelectedFrameMiddle = function() {
		var _ = 0;
		try{ _=(RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].timeline.start/10) + (RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame].timeline.frameLength/10)/2;}
		catch(e) {}
		return _;
	};

	// GET START TIME OF A FRAME
	RVS.F.getTimeAtSelectedFrameStart = function(frame) {
		var _ = 0;
		try{ _=(RVS.L[RVS.selLayers[0]].timeline.frames[frame].timeline.start/10);}
		catch(e) {}
		return _;
	};



	RVS.F.timelineDragsStop = function() {
			RVS.TL.inDrag = false;
			if (!RVS.TL.over) RVS.F.goToIdle();
	};

	RVS.F.updateLoopInputs = function(_) {
		_ = _==undefined ? {s:RVS.SLIDER[RVS.S.slideId].slide.timeline.loop.start, e:RVS.SLIDER[RVS.S.slideId].slide.timeline.loop.end} : _;
		jQuery('#slide_loop_end').val(_.e);
		jQuery('#slide_loop_start').val(_.s);
	};

	RVS.F.updateFixedScrollInputs = function(_) {
		_ = _==undefined ? {s:RVS.SLIDER.settings.scrolltimeline.fixedStart, e:RVS.SLIDER.settings.scrolltimeline.fixedEnd} : _;
		jQuery('#fixed_scroll_end').val(_.e);
		jQuery('#fixed_scroll_start').val(_.s);
	};

	/*
	INIT TIMELINE
	*/
	RVS.F.initTimeLineConstruct = function() {
		// Draw TimeLinear
		tlr = jQuery('#time_linear');
		buildRuler();
		RVS.TL.tS = jQuery('#timeline_settings');
		RVS.TL.ft = jQuery('#frametime');
		RVS.TL.ft_txt = RVS.TL.ft.find('.timebox');
		RVS.TL.mt = jQuery('#maxtime');

		RVS.TL.slts = jQuery('#slidelooptimestart');
		RVS.TL.slts_marker = RVS.TL.slts.find('.timebox_marker');
		RVS.TL.slts_txt = RVS.TL.slts.find('.timebox');
		RVS.TL.slte = jQuery('#slidelooptimeend');
		RVS.TL.slte_marker = RVS.TL.slte.find('.timebox_marker');
		RVS.TL.slte_txt = RVS.TL.slte.find('.timebox');

		RVS.TL.fixs = jQuery('#fixedscrolltimestart');
		RVS.TL.fixs_marker = RVS.TL.fixs.find('.timebox_marker');
		RVS.TL.fixs_txt = RVS.TL.fixs.find('.timebox');

		RVS.TL.fixe = jQuery('#fixedscrolltimeend');
		RVS.TL.fixe_marker = RVS.TL.fixe.find('.timebox_marker');
		RVS.TL.fixe_txt = RVS.TL.fixe.find('.timebox');


		RVS.TL.mtfbg = jQuery('#slide_frame_container .frameswrap');
		RVS.TL.mt_txt = RVS.TL.mt.find('.timebox');
		RVS.TL.ct = jQuery('#currenttime');
		RVS.TL.ct_marker = RVS.TL.ct.find('.timebox_marker');
		RVS.TL.ct_txt = RVS.TL.ct.find('.timebox');
		RVS.TL.ht = jQuery('#hovertime');
		RVS.TL.ht_txt = RVS.TL.ht.find('.timebox');
		RVS.F.updateMaxTime({pos:true, cont:true});
		RVS.TL.TL = jQuery('#timeline');

		//CLICK ON TIMELINE
		tlr.click(function(e) {
			var realOffset = (e.pageX - 310) + RVS.TL._scrollLeft;
			RVS.F.updateCurTime({pos:true, cont:true, left:realOffset,refreshMainTimeLine:true,  caller:"initTimeLineConstruct"});
		});

		RVS.TL.fixs.draggable({
			start:function(evet,ui) {
				RVS.TL.inDrag = true;
			},
			drag:function(event,ui) {
				var el = RVS.TL.fixe.position().left;
				ui.position.left = ui.position.left>=el ? el : ui.position.left;
				ui.position.left = ui.position.left<1 ? 1 : ui.position.left;
				tpGS.gsap.set('.fixedscrolltimemarker',{left:ui.position.left, width:(el - ui.position.left)});
				if (RVS.TL.fixs.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.fixs.addClass("covered");
				else
					RVS.TL.fixs.removeClass("covered");

				RVS.F.updateFixedScrollTimes({cont:true, start:Math.max(0,ui.position.left), end:el});
				RVS.F.updateFixedScrollInputs({e:el*10, s:Math.max(0,ui.position.left)*10});
			},
			stop:function(event,ui) {
				var el = RVS.TL.fixe.position().left;
				ui.position.left = ui.position.left>=el ? el : ui.position.left;
				tpGS.gsap.set('.fixedscrolltimemarker',{left:ui.position.left, width:(el - ui.position.left)});
				if (RVS.TL.fixs.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.fixs.addClass("covered");
				else
					RVS.TL.fixs.removeClass("covered");
				RVS.F.updateFixedScrollTimes({cont:true, start:ui.position.left, end:el});

				RVS.F.openBackupGroup({id:"SliderFixedScrollStartTime",txt:"Fixed Scroll Start Time ",icon:"timer_off"});
				RVS.F.updateSliderObj({path:"settings.scrolltimeline.fixedStart",val:Math.round(ui.position.left*10)});
				RVS.F.closeBackupGroup({id:"SliderFixedScrollStartTime"});
				RVS.F.updateFixedScrollInputs();
			},
			axis:"x"
		});


		RVS.TL.fixe.draggable({
			start:function(evet,ui) {
				RVS.TL.inDrag = true;
			},
			drag:function(event,ui) {
				var el = RVS.TL.fixs.position().left;
				ui.position.left = ui.position.left<=el ? el : ui.position.left;
				tpGS.gsap.set('.fixedscrolltimemarker',{width:(ui.position.left-el)});
				if (RVS.TL.fixe.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.fixe.addClass("covered");
				else
					RVS.TL.fixe.removeClass("covered");
				RVS.F.updateFixedScrollTimes({cont:true, end:ui.position.left, start:el});
				RVS.F.updateFixedScrollInputs({s:el*10, e:ui.position.left*10});
			},
			stop:function(event,ui) {
				var el = RVS.TL.fixs.position().left;
				ui.position.left = ui.position.left<=el ? el : ui.position.left;
				tpGS.gsap.set('.fixedscrolltimemarker',{width:(ui.position.left-el)});
				if (RVS.TL.fixe.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.fixe.addClass("covered");
				else
					RVS.TL.fixe.removeClass("covered");
				RVS.F.updateFixedScrollTimes({cont:true, end:ui.position.left, start:el});
				RVS.F.openBackupGroup({id:"SliderFixedScrollEndTime",txt:"Fixed Scroll End Time ",icon:"timer_off"});
				RVS.F.updateSliderObj({path:"settings.scrolltimeline.fixedEnd",val:Math.round(ui.position.left*10)});
				RVS.F.closeBackupGroup({id:"SliderFixedScrollEndTime"});
				RVS.F.updateFixedScrollInputs();
			},
			axis:"x"
		});



		RVS.TL.slts.draggable({
			start:function(evet,ui) {
				RVS.TL.inDrag = true;
			},
			drag:function(event,ui) {
				var el = RVS.TL.slte.position().left;
				ui.position.left = ui.position.left>=el ? el : ui.position.left;
				tpGS.gsap.set('.slidelooptimemarker',{left:ui.position.left, width:(el - ui.position.left)});
				if (RVS.TL.slts.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.slts.addClass("covered");
				else
					RVS.TL.slts.removeClass("covered");

				RVS.F.updateSlideLoopTimes({cont:true, start:ui.position.left, end:el});
				RVS.F.updateLoopInputs({e:el*10, s:ui.position.left*10});
			},
			stop:function(event,ui) {
				var el = RVS.TL.slte.position().left;
				ui.position.left = ui.position.left>=el ? el : ui.position.left;
				tpGS.gsap.set('.slidelooptimemarker',{left:ui.position.left, width:(el - ui.position.left)});
				if (RVS.TL.slts.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.slts.addClass("covered");
				else
					RVS.TL.slts.removeClass("covered");
				RVS.F.updateSlideLoopTimes({cont:true, start:ui.position.left, end:el});

				RVS.F.openBackupGroup({id:"SlideLoopStartTime",txt:"Slide Loop Start Time ",icon:"timer_off"});
				RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.timeline.loop.start",val:Math.round(ui.position.left*10)});
				RVS.F.closeBackupGroup({id:"SlideLoopStartTime"});
				RVS.F.updateLoopInputs();
			},
			axis:"x"
		});

		RVS.TL.slte.draggable({
			start:function(evet,ui) {
				RVS.TL.inDrag = true;
			},
			drag:function(event,ui) {
				var el = RVS.TL.slts.position().left;
				ui.position.left = ui.position.left<=el ? el : ui.position.left;
				tpGS.gsap.set('.slidelooptimemarker',{width:(ui.position.left-el)});
				if (RVS.TL.slte.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.slte.addClass("covered");
				else
					RVS.TL.slte.removeClass("covered");
				RVS.F.updateSlideLoopTimes({cont:true, end:ui.position.left, start:el});
				RVS.F.updateLoopInputs({s:el*10, e:ui.position.left*10});
			},
			stop:function(event,ui) {
				var el = RVS.TL.slts.position().left;
				ui.position.left = ui.position.left<=el ? el : ui.position.left;
				tpGS.gsap.set('.slidelooptimemarker',{width:(ui.position.left-el)});
				if (RVS.TL.slte.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.slte.addClass("covered");
				else
					RVS.TL.slte.removeClass("covered");
				RVS.F.updateSlideLoopTimes({cont:true, end:ui.position.left, start:el});
				RVS.F.openBackupGroup({id:"SlideLoopEndTime",txt:"Slide Loop End Time ",icon:"timer_off"});
				RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.timeline.loop.end",val:Math.round(ui.position.left*10)});
				RVS.F.closeBackupGroup({id:"SlideLoopEndTime"});
				RVS.F.updateLoopInputs();
			},
			axis:"x"
		});


		// MAX TIME DRAGGABLE
		/*RVS.TL.mt.draggable({
			start: function(event,ui) {
				window._slidelength_ = jQuery('#slide_length');
				window.layersEndWithSlide = RVS.F.getLayersEndWithSlide();
				RVS.TL.ht.addClass("hideme");
				RVS.TL.inDrag = true;
			},
			stop:function(event,ui) {
				RVS.F.timelineDragsStop();
				ui.position.left = RVS.F.setSmallestSlideLength({left:ui.position.left});
				RVS.F.setChildrenTimelines({childLayers:window.layersEndWithSlide, difference: (ui.originalPosition.left - ui.position.left)});
				RVS.F.updateSlideFrames({slidedelay:ui.position.left});
				RVS.SLIDER[RVS.S.slideId].slide.timeline.delay = ui.position.left*10;
				RVS.F.openBackupGroup({id:"SlideEndTime",txt:"Slide EndTime ",icon:"timer_off"});
				RVS.F.backup({path:RVS.S.slideId+".slide.timeline.delay", lastkey:"delay", val:Math.round(ui.position.left*10), old:Math.round(ui.originalPosition.left*10)});
				RVS.F.backupChildren({childLayers:window.layersEndWithSlide});
				RVS.F.closeBackupGroup({id:"SlideEndTime"});
				window._slidelength_[0].value = ui.position.left*10+"ms";

				if (RVS.TL.mt.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.mt.addClass("covered");
				else
					RVS.TL.mt.removeClass("covered");

				RVS.TL.ht.removeClass("hideme");

				//RENDER ALL NEW
				RVS.F.animationMode(true);


			},
			drag:function(event,ui) {
				ui.position.left = RVS.F.setSmallestSlideLength({left:ui.position.left, ignore:true});
				slideMaxTime = ui.position.left;
				RVS.SLIDER[RVS.S.slideId].slide.timeline.delay = ui.position.left*10;
				tpGS.gsap.set(RVS.TL.mt,{left:slideMaxTime});
				tpGS.gsap.set(RVS.TL.mtfbg,{width:slideMaxTime});
				RVS.F.updateMaxTime({pos:false, cont:true, left:ui.position.left});
				RVS.F.setChildrenTimelines({childLayers:window.layersEndWithSlide, difference: (ui.originalPosition.left - ui.position.left)});
				window._slidelength_[0].value = ui.position.left*10+"ms";

				if (RVS.TL.mt.offset().left - RVS.TL.TL.offset().left < 290)
					RVS.TL.mt.addClass("covered");
				else
					RVS.TL.mt.removeClass("covered");

			},
			//containment:"#the_st_cl",
			axis:"x"
		});*/

		// CURRENT TIME DRAGGABLE
		RVS.TL.ct.draggable({
			start: function(event,ui) {
				if (RVS.TL[RVS.S.slideId] && RVS.TL[RVS.S.slideId].main && RVS.TL[RVS.S.slideId] && RVS.TL[RVS.S.slideId].main && RVS.TL[RVS.S.slideId].main.isActive()) return;
				RVS.F.buildMainTimeLine();
				RVS.TL.ht.addClass("hideme");
				RVS.TL.inDrag = true;
			},
			stop:function(event,ui) {
				if (RVS.TL[RVS.S.slideId] && RVS.TL[RVS.S.slideId].main && RVS.TL[RVS.S.slideId].main.isActive()) return;
				if (RVS.TL.ct.offset().left - RVS.TL.TL.offset().left < 265)
					RVS.TL.ct.addClass("covered");
				else
					RVS.TL.ct.removeClass("covered");
				RVS.TL.ht.removeClass("hideme");
				RVS.F.timelineDragsStop();
			},
			drag:function(event,ui) {
				if (RVS.TL[RVS.S.slideId] && RVS.TL[RVS.S.slideId].main && RVS.TL[RVS.S.slideId].main.isActive()) return;
				RVS.F.updateCurTime({pos:false, cont:true, left:ui.position.left,refreshMainTimeLine:true, caller:"Timeline DraG"});
				if (RVS.TL.ct.offset().left - RVS.TL.TL.offset().left < 265)
					RVS.TL.ct.addClass("covered");
				else
					RVS.TL.ct.removeClass("covered");
			},
			containment:".timeline_right_container",
			axis:"x"
		});

		//HOVER TIME
		RVS.DOC.on('mousemove','.stimeline',function(e,a) {	RVS.F.updateHoverTime({pos:true, cont:true, left:(e.pageX - 310)});});
		RVS.DOC.on('mouseenter','.stimeline',function(e,a) {RVS.TL.ht.show();});
		RVS.DOC.on('mouseenter','.timeline_left_container, .context_left, .timeline_right_container',function(e,a) {RVS.TL.ht.hide();});
		RVS.DOC.on('mouseenter','#timeline_settings',function(e,a) {
			RVS.DOC.trigger('previewStopLayerAnimation');
			if (!RVS.TL.over && RVS.TL[RVS.S.slideId].main && RVS.TL.cache!==undefined && RVS.TL.cache.main!==undefined && RVS.TL.cache.main!==0) RVS.F.goToIdle();
			//RVS.F.updateTimeLine({state:"time",time:RVS.TL.cache.main, timeline:"main"});
			RVS.TL.over = true;
		});
		RVS.DOC.on('mouseleave','#timeline_settings',function(e,a) {
			if (RVS.eMode.mode!=="animation") {
				RVS.TL.over = false;
				RVS.TL.ht.hide();
				if (!RVS.TL.inDrag) RVS.F.goToIdle();
			}
		});
	};

	RVS.F.animationMode = function(on) {
		RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
	};

	RVS.F.setSmallestSlideLength = function(_) {
		var minl = Math.max(_.left, beforeLastEnd()/10);
		if (!_.ignore)
			RVS.F.updateMaxTime({pos:true, cont:true, left:minl});
		return minl;
	};

	RVS.F.goToIdle = function(obj) {
		if (!idleMode) {
			RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
			RVS.F.buildMainTimeLine();
			RVS.F.updateCurTime({pos:true, cont:true, force:false, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
		}
		idleMode = true;
	};

	/*
	MAX TIME HANDLING
	*/
	RVS.F.updateMaxTime = function(obj) {
		obj = obj===undefined ? {pos:true, cont:true, left:RVS.F.getSlideLength()} : obj;
		obj.left= obj.left===undefined ? RVS.F.getSlideLength() : obj.left;
		if (obj.pos)
			tpGS.gsap.set(RVS.TL.mt,{left:(obj.left)+"px"});
		if (obj.cont) {
			var _ = pxToSec(obj.left);
			RVS.TL.mt_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';
		}
		RVS.F.updateCoveredTimelines();
	};

	/*
	CURRENT TIME HANDLING
	*/
	RVS.F.updateCurTime = function(obj) {

		if (obj.pos)
			tpGS.gsap.set(RVS.TL.ct,{left:(obj.left)+"px"});
		if (obj.cont) {
			var _ = pxToSec(obj.left);
			obj.left = isNaN(obj.left) ? 0 : obj.left;
			if (obj.left>0) {
				RVS.TL.ct_txt[0].className="timebox inmove";
				RVS.TL.ct_marker[0].className="timebox_marker inmove";
				RVS.TL.ct_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';
			}
			else {
				RVS.TL.ct_txt[0].className="timebox";
				RVS.TL.ct_marker[0].className="timebox_marker";
				RVS.TL.ct_txt[0].innerHTML = 'EDITOR';
			}
			RVS.F.updateCoveredTimelines();
		}
		if (obj.refreshMainTimeLine)
			if (obj.left/100 <=0)
				RVS.F.updateTimeLine({force:obj.force, state:"idle",timeline:"main",caller:"UpdateCurTime A"});
			else
				RVS.F.updateTimeLine({force:obj.force, state:"time",time:(obj.left/100), timeline:"main", freeze:obj.freeze});
	};

	RVS.F.updateSlideLoopTimes = function(obj) {
		if (obj.pos) {
			tpGS.gsap.set(RVS.TL.slts,{left:obj.start+"px"});
			tpGS.gsap.set(RVS.TL.slte,{left:obj.end+"px"});

			tpGS.gsap.set('.slidelooptimemarker',{left:obj.start, width:(obj.end - obj.start)});
		}
		if (obj.cont) {
			var _ = pxToSec(obj.start);
			RVS.TL.slts_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';
			_ = pxToSec(obj.end);
			RVS.TL.slte_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';
		}
	};

	RVS.F.updateFixedScrollTimes = function(obj) {
		if (obj.pos) {
			tpGS.gsap.set(RVS.TL.fixs,{left:obj.start+"px"});
			tpGS.gsap.set(RVS.TL.fixe,{left:obj.end+"px"});

			tpGS.gsap.set('.fixedscrolltimemarker',{left:obj.start, width:(obj.end - obj.start)});
		}
		if (obj.cont) {
			var _ = pxToSec(obj.start);
			RVS.TL.fixs_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';
			_ = pxToSec(obj.end);
			RVS.TL.fixe_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';
		}
	};

	RVS.F.updateHoverTime = function(obj) {
		RVS.TL.hoverTimeLeft = obj.left === undefined ? RVS.TL.hoverTimeLeft : obj.left;
		RVS.TL.hoverTimeLeft = RVS.TL.hoverTimeLeft===undefined ? 0 : RVS.TL.hoverTimeLeft;
		RVS.TL._scrollLeft = RVS.TL._scrollLeft === undefined ? 0 : RVS.TL._scrollLeft;

		if (obj.pos)
			tpGS.gsap.set(RVS.TL.ht,{left:(obj.left)+"px"});
		if (obj.cont) {
			var _ = pxToSec((RVS.TL.hoverTimeLeft + RVS.TL._scrollLeft));
			RVS.TL.ht_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';

		}
	};


	RVS.F.updateFrameTime = function(obj) {
		RVS.TL.frameTimeLeft = obj.left === undefined ? RVS.TL.frameTimeLeft : obj.left;
		RVS.TL.frameTimeLeft = RVS.TL.frameTimeLeft===undefined ? 0 : RVS.TL.frameTimeLeft;
		RVS.TL._scrollLeft = RVS.TL._scrollLeft === undefined ? 0 : RVS.TL._scrollLeft;

		if (obj.pos)
			tpGS.gsap.set(RVS.TL.ft,{left:(obj.left)+"px"});
		if (obj.cont) {
			var _ = pxToSec((RVS.TL.frameTimeLeft));
			RVS.TL.ft_txt[0].innerHTML = '<span class="ctm">'+_.m+'</span>:<span class="cts">'+_.s+'<span>:<span class="ctms">'+_.ms+'</span>';

		}
	};

	/*
	GET ALL FRAMES OF LAYER
	*/
	RVS.F.getLayerFrames = function(_) {
		var kids = _.extend===undefined ? {} : _.extend;
		kids[_.layerid] = { type:RVS.L[_.layerid].type, frames:{}};
		for (var oi in RVS.L[_.layerid].timeline.frameOrder) {
			if(!RVS.L[_.layerid].timeline.frameOrder.hasOwnProperty(oi)) continue;
			var fi =  RVS.L[_.layerid].timeline.frameOrder[oi].id;
			if (_.afterStart!==undefined) {
				if (RVS.L[_.layerid].timeline.frames[fi].timeline.start>_.afterStart && fi!=="frame_999")
					kids[_.layerid].frames[fi] = RVS.L[_.layerid].timeline.frames[fi].timeline.start;
				if (fi==="frame_999" && _.include999===true)
					kids[_.layerid].frames[fi] = RVS.L[_.layerid].timeline.frames[fi].timeline.start;
			} else {
				kids[_.layerid].frames[fi] = RVS.L[_.layerid].timeline.frames[fi].timeline.start;
			}
		}
		return kids;
	};


	/*
	GET ALL LAYERS LAST FRAME, AND MARK THE ONE WITH END WITH SLIDE ATTRIBUTES
	*/
	RVS.F.getLayersEndWithSlide = function() {
		var kids = {};
		for (var li in RVS.L) {
			if(!RVS.L.hasOwnProperty(li)) continue;
			//if (RVS.L[li].timeline.frames.frame_999.timeline.endWithSlide)
				if (li!=="top" && li!=="bottom" && li!=="middle")
					kids[li] = {
							type:RVS.L[li].type,
							endWithSlide:RVS.L[li].timeline.frames.frame_999.timeline.endWithSlide,
							frames:{frame_999:RVS.L[li].timeline.frames.frame_999.timeline.start}
					};
		}
		return kids;
	};

	RVS.F.clearLayerAnimation = function(_) {
		RVS.H[_.layerid].timeline.clear();
	};

	/*
	FORMAT THE TIME TO HUMAN READABLE TIME
	*/
	RVS.F.formatTime = function(d) {
		d = d*1000;
		var ms = parseInt((d%1000)/10),
            s = parseInt((d/1000)%60),
            m = parseInt((d/(1000*60))%60);
        ms = (ms < 10) ? "0" + ms : ms;
        m = (m < 10) ? "0" + m : m;
        s = (s < 10) ? "0" + s : s;
        return  m+":"+s+":"+ms;
	};
	/*
	UPDATE SPLITTED OR NONE SPLITTED CONTENT
	*/
	RVS.F.updateSplitContent = function(_) {
		var split = false;
		if (RVS.H[_.layerid].splitText) RVS.H[_.layerid].splitText.revert();
		if (RVS.L[_.layerid].type==="text" || RVS.L[_.layerid].type==="button") {
			for (var frame  in RVS.L[_.layerid].timeline.frames) {
				if(!RVS.L[_.layerid].timeline.frames.hasOwnProperty(frame)) continue;
				if (RVS.L[_.layerid].timeline.frames[frame].chars.use || RVS.L[_.layerid].timeline.frames[frame].words.use || RVS.L[_.layerid].timeline.frames[frame].lines.use) {
					split = true;
					break;
				}
			}
			if (split)
				RVS.H[_.layerid].splitText = new tpGS.SplitText(RVS.H[_.layerid].c,{type:"lines,words,chars",wordsClass:"rs_splitted_words",linesClass:"rs_splitted_lines",charsClass:"rs_splitted_chars"});
			else
				RVS.H[_.layerid].splitText = undefined;
		}
		else
			RVS.H[_.layerid].splitText = undefined;
		return split;
	};

	/*
	GET THE FRAMEORDER IN THE TIMELINE
	*/
	RVS.F.getFrameOrder = function(_) {
		RVS.L[_.layerid].timeline.frameOrder = [];
		for (var frame in RVS.L[_.layerid].timeline.frames) {
			if(!RVS.L[_.layerid].timeline.frames.hasOwnProperty(frame)) continue;
			RVS.L[_.layerid].timeline.frameOrder.push({id:frame, start:frame==="frame_0" ? -1 : RVS.L[_.layerid].timeline.frames[frame].timeline.start});
		}

		RVS.L[_.layerid].timeline.frameOrder.sort(function(a,b) { return a.start - b.start;});

		RVS.L[_.layerid].timeline.frameToIdle = RVS.L[_.layerid].timeline.frameToIdle===undefined ? "frame_1" : RVS.L[_.layerid].timeline.frameToIdle;



	};

	/*
	 AT COLUMN BG, BORDER WE NEED RO REMOVE VALUES SINCE BG IS SET ALREADY
	 */
	function reduceColumn(_) {
		var r = RVS.F.safeExtend(true,{},_);
		delete r.borderWidth;
		delete r.borderStyle;
		delete r.borderColor;
		delete r.backgroundColor;
		delete r.background;
		delete r.backgroundImage;
		return r;
	}
	function convertDirection(_) {return _===undefined ? "start" : _==="backward" ? "end" : _==="middletoedge" ? "center" : _==="edgetomiddle" ? "edge" : _};

	// reusable function that you can wrap around your stagger vars and define an "offset" value that'll get added to the 2nd half of the values
	function offsetStagger(vars) {
		let distributor = tpGS.gsap.utils.distribute(vars);
		return function(i, target, targets) {
			return distributor(i, target, targets) + (i <= targets.length / 2 ? 0 : vars.offset || 0);
		}
	}
	/*
	RENDER LAYER ANIMATIONS
	*/
	RVS.F.renderLayerAnimation = function(_) {

		var lh = RVS.H[_.layerid],
			l = RVS.L[_.layerid],
			tPE=600; // transformPerspective

		if (RVS.TL[RVS.S.slideId].layers===undefined || RVS.TL[RVS.S.slideId].layers[_.layerid]===undefined) return;
		if (lh===undefined || l.timeline===undefined || l.timeline.frames===undefined )  return;

		if (lh.timeline) lh.timeline.pause("frame_IDLE");

		lh.timeline = new tpGS.TimelineMax({paused:true});

		var split = /*_.timeline==="full" ? lh.splitText!==undefined : */ l.type==="text" || l.type==="button" ? /*l.type!=="row" && l.type!=="group" && l.type!=="column" ? */ RVS.F.updateSplitContent({layerid:_.layerid}) : false;
		l.timeline.split = split;
		RVS.F.getFrameOrder({layerid:_.layerid});
		/* var fOrderLen = l.timeline.frameOrder.length, */
		var firstframe = RVS.F.getFirstFrame({layerid:_.layerid});

		for (var oi in l.timeline.frameOrder) {
			if(!l.timeline.frameOrder.hasOwnProperty(oi)) continue;

			var fi = l.timeline.frameOrder[oi].id;

			if (fi ==="frame_0") continue;



			var	_frameObj = _.frameObj===undefined || _.frame!==fi ?  l.timeline.frames[fi] : _.frameObj;

			l.timeline.sessionFilterUsed = RVS.F.checkGlobalFiltersOnLayer(_.layerid);

			if (fi ==="frame_999" && l.timeline.frames.frame_999.timeline.auto) {

				_frameObj = RVS.F.safeExtend(true,{},l.timeline.frames.frame_999);
				_frameObj.transform = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.transform);
				_frameObj.mask = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.mask);
				_frameObj.words = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.words);
				_frameObj.lines = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.lines);
				_frameObj.chars = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.chars);
				_frameObj.sfx = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.sfx);
				_frameObj.filter = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.filter);
				_frameObj.color = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.color);
				_frameObj.bgcolor = RVS.F.safeExtend(true,{},l.timeline.frames.frame_0.bgcolor);

			}

			var	_fromFrameObj = fi===firstframe ? l.timeline.frames.frame_0 : undefined,
				_frame = RVS.TL[RVS.S.slideId].layers[_.layerid][fi],
				aObj = lh.c,
				sfx = checkSFXAnimations(_frameObj.sfx.effect,lh.m,_frameObj.timeline.ease),
				tt = new tpGS.TimelineMax(),
				speed = _frameObj.timeline.speed/1000,
				/* idlebgcolor = l.idle.backgroundColor, */
				splitDelay = 0;

			_frame.timeline = new tpGS.TimelineMax();


			if (sfx.type==="block") {
				sfx.ft[0].background = window.RSColor.get(_frameObj.sfx.color);
				_frame.timeline.add(tpGS.gsap.fromTo(sfx.bmask_in,speed/2, sfx.ft[0], sfx.ft[1] ,0));
				_frame.timeline.add(tpGS.gsap.fromTo(sfx.bmask_in,speed/2, sfx.ft[1], sfx.t, speed/2));
				if (fi==="frame_0" || fi==="frame_1")
					_frame.timeline.add(tt.staggerFromTo(aObj,0.05,{ autoAlpha:0},{autoAlpha:1,delay:speed/2},0),0);
				else
				if (fi==="frame_999")
					_frame.timeline.add(tt.staggerFromTo(aObj,0.05,{ autoAlpha:1},{autoAlpha:0,delay:speed/2},0),0);
			}



			var anim = convertTransformValues({sessionFilterUsed:l.timeline.sessionFilterUsed, frame:_frameObj, layerid:_.layerid, ease:_frameObj.timeline.ease, splitAmount:aObj.length,target:fi}),
				from = fi===firstframe ? convertTransformValues({sessionFilterUsed:l.timeline.sessionFilterUsed, frame:_fromFrameObj, layerid:_.layerid,ease:_frameObj.timeline.ease, splitAmount:aObj.length,target:"frame_0"}) : undefined,
				mask = _frameObj.mask.use=="true" || _frameObj.mask.use==true ? convertTransformValues({frame:{transform:{x:_frameObj.mask.x, y:_frameObj.mask.y, clip:_frameObj.mask.clip}}, layerid:_.layerid, ease:anim.ease,target:"mask"}) : undefined,
				frommask = fi===firstframe ? convertTransformValues({frame:{transform:{x:_fromFrameObj.mask.x, y:_fromFrameObj.mask.y, clip:_fromFrameObj.mask.clip}}, layerid:_.layerid, ease:anim.ease,target:"frommask"}) : undefined,
				origEase = anim.ease;


			// SET COLOR ON LAYER (TO AND FROM)
			if (_frameObj.color!==undefined && _frameObj.color.use)
				anim.color = window.RSColor.get(_frameObj.color.color);
			else
				anim.color = window.RSColor.get(l.idle.color[RVS.screen].v);

			if (_fromFrameObj!==undefined) {
				if (_fromFrameObj.color!==undefined && _fromFrameObj.color.use)
					from.color = window.RSColor.get(_fromFrameObj.color.color);
				else
					from.color = window.RSColor.get(l.idle.color[RVS.screen].v);
			}



			// SET BACKGROUNDCOLOR ON LAYER (TO AND FROM)
			if (_frameObj.bgcolor!==undefined && _frameObj.bgcolor.use) {
				var bgval = window.RSColor.get(_frameObj.bgcolor.backgroundColor);
				if (bgval.indexOf("gradient")>=0) anim.background = bgval;
				else anim.backgroundColor = bgval;

			} else {
				var bgval = window.RSColor.get(l.idle.backgroundColor);
				if (bgval.indexOf("gradient")>=0) anim.background = bgval;
				else anim.backgroundColor = bgval;
			}

			if (_fromFrameObj!==undefined) {
				if (_fromFrameObj.bgcolor!==undefined && _fromFrameObj.bgcolor.use) {
					var bgval = window.RSColor.get(_fromFrameObj.bgcolor.backgroundColor);
					if (bgval.indexOf("gradient")>=0) from.background = bgval;
					else from.backgroundColor = bgval;
				} else {
					var bgval = window.RSColor.get(l.idle.backgroundColor);
					if (bgval.indexOf("gradient")>=0) from.background = bgval;
					else from.backgroundColor = bgval;
				}
			}

			var maxSplitDelay = 0;

			// ANIMATE CHARS, WORDS, LINES
			if (split) {
				for (var i in splitTypes) {
					if(!splitTypes.hasOwnProperty(i)) continue;
					if (_frameObj[splitTypes[i]].use && !_.quickRendering) {

						var sObj = lh.splitText[splitTypes[i]], //,_frameObj[splitTypes[i]].direction),
							sanim = convertTransformValues({frame:_frameObj, source:splitTypes[i], ease:origEase, layerid:_.layerid,splitAmount:sObj.length, target:fi+"_"+splitTypes[i]}),
							sfrom = (fi===firstframe ? convertTransformValues({frame:_fromFrameObj,  ease:sanim.ease, source:splitTypes[i], layerid:_.layerid,splitAmount:sObj.length, target:"frame_0_"+splitTypes[i]}) : undefined);

						splitDelay =  parseInt(_frameObj[splitTypes[i]].delay,0) /100;



						// SET COLOR ON SPLIT  (TO AND FROM)

						if (window.RSColor.get(l.idle.color[RVS.screen].v)!==anim.color || fi!=="frame_1") sanim.color = anim.color; 	// ADDED IF TO RESPECT INLINE STYLES
						if (from!==undefined && window.RSColor.get(l.idle.color[RVS.screen].v)!==from.color) sfrom.color = from.color;	// ADDED IF TO RESPECT INLINE STYLES
						if (sfrom!==undefined && sfrom.color!==anim.color) sanim.color=anim.color;

						var	$anim = getCycles(RVS.F.safeExtend(true,{},sanim)),
							$from = fi===firstframe ? getCycles(RVS.F.safeExtend(true,{},sfrom)) : undefined,
							dir = convertDirection(_frameObj[splitTypes[i]].direction);
						delete $anim.grayscale;
						delete $anim.brightness;
						if ($from!==undefined) {
							delete $from.grayscale;
							delete $from.brightness;
						}


						$anim.stagger = dir==="center" || dir==="edge" ? offsetStagger({each:splitDelay, offset:splitDelay/2, from:dir}) : {each:splitDelay, from:convertDirection(_frameObj[splitTypes[i]].direction)};
						$anim.duration = speed;
						if ($from!==undefined) delete $from.dir;
						if (fi===firstframe) _frame.timeline.add(tt.fromTo(sObj,$from,$anim),0); else _frame.timeline.add(tt.to(sObj,$anim),0);
						maxSplitDelay = Math.max(maxSplitDelay, (sObj.length * splitDelay));
					}

				}
			}

			//SPEED SYNC WITH THE SPLIT SPEEDS IF NECESSARY
			speed = speed + maxSplitDelay;
			tPE = RVS.SLIDER.settings.general.perspectiveType==="local" ? from!==undefined && from.transformPerspective!==undefined ? from.transformPerspective : anim.transformPerspective!==undefined ? anim.transformPerspective : RVS.SLIDER.settings.general.perspective : RVS.SLIDER.settings.general.perspective;


			// ANIMATE MASK
			if (mask!==undefined) {
				mask.overflow = "hidden";
				mask.rotationX = l.idle.rotationX;
				mask.rotationY = l.idle.rotationY;
				mask.rotationZ = l.idle.rotationZ;
				mask.opacity = l.idle.opacity;
				mask.transformPerspective = tPE;

				if (fi===firstframe) {
					frommask.rotationX = l.idle.rotationX;
					frommask.rotationY = l.idle.rotationY;
					frommask.rotationZ = l.idle.rotationZ;
					frommask.opacity = l.idle.opacity;
					_frame.timeline.add(tpGS.gsap.fromTo([lh.m,lh.bgmask],speed,frommask,mask),0);
				} else {
					_frame.timeline.add(tpGS.gsap.to([lh.m,lh.bgmask],speed,mask),0);
				}
			} else {
				_frame.timeline.add(tpGS.gsap.to(lh.m,0.001,{transformPerspective:tPE, filter:"none", x:0, y:0, opacity:l.idle.opacity, rotationX:l.idle.rotationX,  rotationY:l.idle.rotationY, rotationZ:l.idle.rotationZ, overflow:"visible"}),0);
			}


			anim.force3D="auto";

			delete anim.clipB;

			// ANIMATE ELEMENT
			if (fi===firstframe) {
				delete from.clipB;

				if (lh.bg!==undefined) _frame.timeline.fromTo(lh.bg,speed,from,anim,0);
				if (lh.bg!==undefined && l.type==="column")
					_frame.timeline.fromTo(aObj,speed,reduceColumn(from),reduceColumn(anim),0);
				else
					_frame.timeline.fromTo(aObj,speed,from,anim,0);

			} else {
				if (lh.bg!==undefined) _frame.timeline.to(lh.bg,speed,anim,0);

				if (lh.bg!==undefined && l.type==="column")
					_frame.timeline.to(aObj, speed, reduceColumn(anim),0);
				else
					_frame.timeline.to(aObj, speed, anim,0);
			}

			if (origEase!==undefined && Array.isArray(origEase) && origEase.indexOf("SFXBounce")>=0) _frame.timeline.to(aObj,speed,{scaleY:0.5,scaleX:1.3,ease:anim.ease+"-squash",transformOrigin:"bottom"},0.0001);

			if (_.timeline==="full") {
				var pos = parseInt(_frameObj.timeline.start,0)/1000;
				lh.timeline.addLabel(fi,pos);
				lh.timeline.add(_frame.timeline,pos);
				lh.timeline.addLabel(fi+"_end","+=0.01");
				if (l.timeline.frameToIdle === fi) lh.timeline.addLabel("frame_IDLE");
			} else {
				lh.timeline.addLabel(fi);
				lh.timeline.add(_frame.timeline);
				if (fi===_.frame) {
					lh.timeline.addPause(fi+"_end+=0.5", function(frame) {this.play(frame);},[_.frame]);
				} else {
					lh.timeline.addLabel(fi+"_end");
					if (l.timeline.frameToIdle === fi) lh.timeline.addLabel("frame_IDLE");
					if (l.timeline.loop.use) {
						lh.timeline.addPause(fi+"_end+="+l.timeline.loop.speed/500,function() {this.play();});
						if (fi=="frame_999") lh.timeline.addPause(fi+"_end+=0.5",function() {this.play(0);});
					} else
						lh.timeline.addPause(fi+"_end+=0.5",function() {this.play();});

				}
			}

		}



		// RENDER HOVER ANIMATION
		if ((l.hover.usehover=='true' || l.hover.usehover==true || l.hover.usehover=="desktop") && lh.htr) {
			lh.hover = new tpGS.TimelineMax();
			lh.hover.pause();
			lh.htr.ease = l.hover.ease;
			var hoverspeed = parseInt(l.hover.speed,0)/1000;
			hoverspeed = hoverspeed===0 ? 0.00001 : hoverspeed;
			if (l.type==="column" || l.type==="row") lh.hover.to(lh.bg,hoverspeed,RVS.F.safeExtend(true,{},lh.htr),0);
			if ((l.type==="text" || l.type==="button") && l.timeline.split && lh.splitText!==undefined)
				lh.hover.to([lh.splitText.lines, lh.splitText.words, lh.splitText.chars],hoverspeed,{ color:lh.htr.color,ease:lh.htr.ease},0);

			if (l.type==="column")
				lh.hover.to(lh.c,hoverspeed,reduceColumn(RVS.F.safeExtend(true,{},lh.htr)),0);
			else
				lh.hover.to(lh.c,hoverspeed,RVS.F.safeExtend(true,{},lh.htr),0);
			if (l.type==="svg") {
				lh.hover.to(lh.svg,hoverspeed,{	fill:window.RSColor.get(l.hover.svg.color),
											stroke:window.RSColor.get(l.hover.svg.strokeColor),
											"stroke-width":l.hover.svg.strokeWidth,
											"stroke-dasharray":RVS.F.getDashArray(l.hover.svg.strokeDashArray),
											"stroke-dashoffset":(l.hover.svg.strokeDashOffset===undefined ? 0 : l.hover.svg.strokeDashOffset)
										},0);
				lh.hover.to(lh.svgPath,hoverspeed,{ fill:window.RSColor.get(l.hover.svg.color)},0);
			}

			lh.hover.to([lh.m,lh.bgmask],hoverspeed,{overflow:l.hover.usehovermask ? "hidden" : "visible"},0);

			//SET HOVER ANIMATION
			if (!lh.hoverlistener) {
				lh.hoverlistener= true;
				lh.w.hover(function() {
					lh.hover.play();
				}, function() {
					if (RVS.eMode.mode!=="hover" || !lh.w.hasClass("selected")) lh.hover.reverse();
				});

				if (RVS.eMode.mode==="hover" && jQuery.inArray(parseInt(l.uid,0),RVS.selLayers)>=0)
					lh.hover.play();
				else
				if (lh.hover.time()>0)  lh.hover.reverse();

			}
		}	else
		if (lh.hoverlistener) {
			lh.hoverlistener= false;
			lh.w.unbind('hover');
		}


		//RENDER LOOP ANIMATION
		if (l.timeline.loop.use && (!idleMode || RVS.eMode.mode==="animation")) {
			var lif = l.timeline.loop.frame_0,
				lof = l.timeline.loop.frame_999,
				/* repeat = -1, */
				looptime = new tpGS.TimelineMax({}),
				loopmove = new tpGS.TimelineMax({repeat:-1,yoyo:l.timeline.loop.yoyo_move}),
				looprotate = new tpGS.TimelineMax({repeat:-1,yoyo:l.timeline.loop.yoyo_rotate}),
				loopscale = new tpGS.TimelineMax({repeat:-1,yoyo:l.timeline.loop.yoyo_scale}),
				loopfilter = new tpGS.TimelineMax({repeat:-1,yoyo:l.timeline.loop.yoyo_filter}),
				lspeed = parseInt(l.timeline.loop.speed,0)/1000,
				lstart = parseInt(l.timeline.loop.start)/1000 || 0,
				lsspeed = 0.2,
				lssstart = lstart+lsspeed,
				loopresetfilter = 'blur(0px) grayscale(0%) brightness(100%)',
				loopsfilter = 'blur('+parseInt(lif.blur || 0,0)+'px) grayscale('+parseInt(lif.grayscale || 0 ,0)+'%) brightness('+parseInt(lif.brightness || 100,0)+'%)',
				loopendfilter = 'blur('+(lof.blur || 0)+'px) grayscale('+(lof.grayscale || 0)+'%) brightness('+(lof.brightness || 100)+'%)';

			if (loopsfilter === 'blur(0px) grayscale(0%) brightness(100%)' && loopendfilter === 'blur(0px) grayscale(0%) brightness(100%)') {
				loopsfilter  ="none";
				loopendfilter = "none";
				loopresetfilter = "none";
			}

			looptime.add(loopmove,0);
			looptime.add(looprotate,0);
			looptime.add(loopscale,0);
			looptime.add(loopfilter,0);

			//LOOP MOVE ANIMATION
			if (!l.timeline.loop.curved) {
				//Move in First Position
				lh.timeline.fromTo(lh.lp,lsspeed,{'-webkit-filter':loopresetfilter, 'filter':loopresetfilter, x:0,y:0,z:0, scale:1,skewX:0, skewY:0, rotationX:0, rotationY:0,rotationZ:0, transformPerspective:tPE, transformOrigin:l.timeline.loop.originX+" "+l.timeline.loop.originY+" "+l.timeline.loop.originZ, opacity:1},
					RVS.F.checkLoopSkew({ x:lif.x, y:lif.y, z:lif.z, scaleX:lif.scaleX, skewX:lif.skewX, skewY:lif.skewY, scaleY:lif.scaleY,rotationX:lif.rotationX,rotationY:lif.rotationY,rotationZ:lif.rotationZ, ease:"sine.out", opacity:lif.opacity,'-webkit-filter':loopsfilter, 'filter':loopsfilter}),lstart);
				loopmove.to(lh.lp,(l.timeline.loop.yoyo_move ? lspeed/2 : lspeed),{x:lof.x, y:lof.y, z:lof.z, ease:l.timeline.loop.ease});
			} else {
				//CALCULATE EDGES
				var sangle = parseInt(l.timeline.loop.radiusAngle,0) || 0,
					v = [{x:parseInt(lif.x,0)-parseInt(lif.xr,0), y:0, z:parseInt(lif.z,0)-parseInt(lif.zr,0)},	{x:0, y:parseInt(lif.y,0)+parseInt(lif.yr,0), z:0}, {x:parseInt(lof.x,0)+parseInt(lof.xr,0), y:0, z:parseInt(lof.z,0)+parseInt(lof.zr,0)},{x:0, y:parseInt(lof.y,0)-parseInt(lof.yr,0), z:0}],
					motionPath = {type:"thru",curviness:l.timeline.loop.curviness,path:[],autoRotate:l.timeline.loop.autoRotate};
				for (var bind in v) {
					if(!v.hasOwnProperty(bind)) continue;
					motionPath.path[bind] = v[sangle];
					sangle++;
					sangle = sangle==v.length ? 0 : sangle;
				}
				//Move in First Position
				lh.timeline.fromTo(lh.lp,lsspeed,{ '-webkit-filter':loopresetfilter, 'filter':loopresetfilter, x:0,y:0,z:0, scale:1,skewX:0, skewY:0, rotationX:0, rotationY:0,rotationZ:0, transformPerspective:tPE, transformOrigin:l.timeline.loop.originX+" "+l.timeline.loop.originY+" "+l.timeline.loop.originZ, opacity:1},{ x:motionPath.path[3].x, y:motionPath.path[3].y, z:motionPath.path[3].z, scaleX:lif.scaleX, skewX:lif.skewX, skewY:lif.skewY, scaleY:lif.scaleY,rotationX:lif.rotationX,rotationY:lif.rotationY,rotationZ:lif.rotationZ, '-webkit-filter':loopsfilter, 'filter':loopsfilter, ease:"sine.out", opacity:lif.opacity},lstart);
				loopmove.to(lh.lp,(l.timeline.loop.yoyo_move ? lspeed/2 : lspeed),{motionPath:motionPath, ease:l.timeline.loop.ease});
			}

			//LOOP ROTATE ANIMATION
			looprotate.to(lh.lp,(l.timeline.loop.yoyo_rotate ? lspeed/2 : lspeed),{rotationX:lof.rotationX,rotationY:lof.rotationY,rotationZ:lof.rotationZ, ease:l.timeline.loop.ease});
			//LOOP SCALE ANIMATION
			loopscale.to(lh.lp,(l.timeline.loop.yoyo_scale ? lspeed/2 : lspeed),RVS.F.checkLoopSkew({scaleX:lof.scaleX, scaleY:lof.scaleY, skewX:lof.skewX, skewY:lof.skewY, ease:l.timeline.loop.ease}));

			//LOOP FILTER ANIMATION
			var filtanim = { opacity:lof.opacity ,ease:l.timeline.loop.ease, '-webkit-filter':loopendfilter, 'filter':loopendfilter};
			loopfilter.to(lh.lp,(l.timeline.loop.yoyo_filter ? lspeed/2 : lspeed),filtanim);

			//WELCHE WERTE MUSS ICH HIN UND HER SCHIEBEN ??
			lh.timeline.add(looptime,lssstart);
		} else {
			loopmove = new tpGS.TimelineMax({});
			loopmove.set(lh.lp,{'-webkit-filter':'blur(0px) grayscale(0%) brightness(100%)', 'filter':'blur(0px) grayscale(0%) brightness(100%)', x:0,y:0,z:0, scale:1,skewX:0, skewY:0, rotationX:0, rotationY:0,rotationZ:0, transformPerspective:tPE, transformOrigin:"50% 50%", opacity:1});
			lh.timeline.add(loopmove,0);
		}

		if (_.mode!=="atstart") {
			if (RVS.S.keyFrame==="0")
				lh.timeline.pause("frame_0");
			else
			if (RVS.S.keyFrame==="idle")
				lh.timeline.pause("frame_IDLE");
			else {
				lh.timeline.pause(RVS.S.keyFrame+"_end");
			}
		}


		if (_.time!==undefined)
			lh.timeline.time(_.time);

		if (_.timeline==="loopsingleframe")
			lh.timeline.play(_.frame);
		else
		if (_.timeline!=="full") {
			lh.timeline.eventCallback("onComplete",function() {this.restart();});
		}
	};

	RVS.F.checkLoopSkew = function(_) {
		if (_.skewX===undefined) delete _.skewX;
		if (_.skewY===undefined) delete _.skewY;
		return _;
	}

	RVS.F.buildFullLayerAnimation = function(mode) {
		for (var li in RVS.L) {
			if(!RVS.L.hasOwnProperty(li)) continue;
			if (RVS.L[li].uid!==undefined) RVS.F.renderLayerAnimation({layerid:li, timeline:"full",mode:mode});
		}
	};


	// PLAY SHORT LAYER ANIMATIONS
	RVS.F.playLayerAnimation = function(_) {
		if(_.time!==undefined)
			RVS.H[_.layerid].timeline.play(_.time);
		else
			RVS.H[_.layerid].timeline.play(0);
		animatedLayers.push(_.layerid);
	};

	// STOP SINGLE LAYER ANIMATION
	RVS.F.stopLayerAnimation = function(_) {
		if (RVS.H[_.layerid]===undefined) return;
		if (RVS.H[_.layerid].timeline) RVS.H[_.layerid].timeline.pause("frame_IDLE");
		animatedLayers = RVS.F.rArray(animatedLayers,parseInt(_.layerid,0));
	};

	// STOP ALL LAYER ANIAMTION
	RVS.F.stopAllLayerAnimation = function() {
		var wassomething = animatedLayers.length;
		while (animatedLayers.length>0) {
			RVS.F.stopLayerAnimation({layerid:animatedLayers[0]});
		}
		if (wassomething>0)
			if (RVS.TL.cache.main<=0)
				RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"stopAllLayerAnimation"});
			else
				RVS.F.updateTimeLine({force:true, state:"time",time:RVS.TL.cache.main, timeline:"main", forceFullLayerRender:true, updateCurTime:true});
	};

	RVS.F.stopAndPauseAllLayerAnimation = function() {
		RVS.S.shwLayerAnim = false;
		RVS.F.changeSwitchState({el:document.getElementById("layer_simulator"),state:"play"});
		RVS.F.changeSwitchState({el:document.getElementById("layer_simulator_loop"),state:"play"});
		RVS.F.stopAllLayerAnimation();
	};
	////////////////////////////////////////
	// 		LAYER ANIMATION FUNCTIONS	 //
	////////////////////////////////////////
	function getCycles(anim) {
	 	var _;
		for (var a in anim) {
			if (typeof anim[a] === "string" && anim[a].indexOf("|")>=0) {
				_=anim[a].replace("[","").replace("]","").split("|");
				anim[a]=new function(index) { return tpGS.gsap.utils.wrap(_,index)};
			}
		}
		return anim;
	};

	function shuffleArray(array) {
	  var currentIndex = array.length, temporaryValue, randomIndex;

	  // While there remain elements to shuffle...
	  while (0 !== currentIndex) {

	    // Pick a remaining element...
	    randomIndex = Math.floor(Math.random() * currentIndex);
	    currentIndex -= 1;

	    // And swap it with the current element.
	    temporaryValue = array[currentIndex];
	    array[currentIndex] = array[randomIndex];
	    array[randomIndex] = temporaryValue;
	  }
	  return array;
	}

	// SFX ANIMATIONS
	function checkSFXAnimations(effect,mask,easedata) {

		// BLOCK SFX ANIMATIONS
		if (effect!==undefined && effect.indexOf("block")>=0) {
			var sfx = {};

			if (mask.find('.tp-blockmask_in').length===0) {
				mask.append('<div class="tp-blockmask_in"></div>');
				mask.append('<div class="tp-blockmask_out"></div>');
			}
			easedata=easedata===undefined ? "power3.inOut" : easedata;

			sfx.ft = [{scaleY:1,scaleX:0,transformOrigin:"0% 50%"},{scaleY:1,scaleX:1,ease:easedata,immediateRender:false}];
			sfx.t =  {scaleY:1,scaleX:0,transformOrigin:"100% 50%",ease:easedata,immediateRender:false};
			sfx.bmask_in = mask.find('.tp-blockmask_in');
			sfx.bmask_out = mask.find('.tp-blockmask_out');
			sfx.type = "block";

			switch (effect) {
				case "blocktoleft":
				case "blockfromright":
					sfx.ft[0].transformOrigin = "100% 50%";
					sfx.t.transformOrigin = "0% 50%";
				break;

				case "blockfromtop":
				case "blocktobottom":
					sfx.ft = [{scaleX:1,scaleY:0,transformOrigin:"50% 0%"},{scaleX:1,scaleY:1,ease:easedata,immediateRender:false}];
					sfx.t =  {scaleX:1,scaleY:0,transformOrigin:"50% 100%",ease:easedata,immediateRender:false};
				break;

				case "blocktotop":
				case "blockfrombottom":
					sfx.ft = [{scaleX:1,scaleY:0,transformOrigin:"50% 100%"},{scaleX:1,scaleY:1,ease:easedata,immediateRender:false}];
					sfx.t =  {scaleX:1,scaleY:0,transformOrigin:"50% 0%",ease:easedata,immediateRender:false};
				break;
			}
			sfx.ft[1].overwrite = "auto";
			sfx.t.overwrite = "auto";

			return sfx;
		} else {
			mask.find('.tp-blockmask').remove();
			return false;
		}
	}


	function convertTransformValues(_) {

		var a = _.source === undefined ? RVS.F.safeExtend(true,{},_.frame.transform) : RVS.F.safeExtend(true,{},_.frame[_.source]),
			dim = {height:RVS.H[_.layerid].w.height(), width:RVS.H[_.layerid].w.width()},
			pos = RVS.H[_.layerid].w.position(),
			loffset = RVS.L[_.layerid].behavior.baseAlign==="slide" ? RVS.S.layer_grid_offset.left : 0,
			wrapperheight = RVS.L[_.layerid].group.puid===-1 ? RVS.S.lgh : RVS.H[RVS.L[_.layerid].group.puid]===undefined ? RVS.S.lgh  : RVS.H[RVS.L[_.layerid].group.puid].w.height(),
			wrapperwidth = RVS.L[_.layerid].group.puid===-1 ? RVS.S.lgw : RVS.H[RVS.L[_.layerid].group.puid]===undefined ? RVS.S.lgw  : RVS.H[RVS.L[_.layerid].group.puid].w.width(),
			torig = {originX:"50%", originY:"50%", originZ:"0"};


		for (var atr in a) {
			if(!a.hasOwnProperty(atr)) continue;



			a[atr] = (typeof a[atr]==="object") ? a[atr][RVS.screen].v : a[atr];

			if (a[atr] === "inherit" || atr==="delay" || atr==="direction" || atr==="use" || atr==="fuse") delete a[atr];	// NOT FOR ANIMATION
			else if (atr==="originX" || atr==="originY" || atr==="originZ") { // ORIGINS CAN BE IGNORED
				torig[atr] = a[atr];
				delete a[atr];
			} else {
				if ((atr==="perspective" || atr==="transformPerspective") && (RVS.SLIDER.settings.general.perspectiveType==="global" || RVS.SLIDER.settings.general.perspectiveType==="isometric")) a[atr] =  RVS.SLIDER.settings.general.perspectiveType==="isometric" ? 0 : RVS.SLIDER.settings.general.perspective;
				if (jQuery.isNumeric(a[atr],0)) a[atr] = a[atr];  // NUMERIC ?
				else
				if (a[atr].match(/[\{\}]/g)) a[atr] = "random("+a[atr].replace(/[\{&&\}]+/g,'')+")"; // RANDOM RANGE ?
				else
				if (a[atr].match(/%/g) && jQuery.isNumeric(parseInt(a[atr],0))) a[atr] = atr=="x" ? dim.width*parseInt(a[atr],0)/100 : atr=="y" ? dim.height*parseInt(a[atr],0)/100 : a[atr]; // % BASED ?
				else
				if (a[atr].match(/[\[\]]/g)) {	// CYCLE ?
					var cycar = a[atr].replace("[","").replace("]","").split("|");
					a[atr] = new function(index) { return tpGS.gsap.utils.wrap(cycar,index)};
				} else
				switch (a[atr]) {
					case "top":a[atr] = 0-dim.height-pos.top;break;
					case "bottom":a[atr] = wrapperheight-pos.top;break;
					case "left":a[atr] = loffset-dim.width-pos.left;break;
					case "right":a[atr] = wrapperwidth-pos.left;break;
					case "middle": case "center": a[atr] = atr==="x" ? wrapperwidth/2 - pos.left - dim.width/2 : atr==="y" ? wrapperheight/2 - pos.top - dim.height/2 : a[atr];break;
				}
			}

			// MANAGE SKEW CALCULATION
			if (atr==="skewX" && a[atr]!==undefined && parseFloat(a[atr])!==0) {
				a['scaleY'] = a['scaleY']===undefined ? 1 : parseFloat(a['scaleY']);
				a['scaleY'] *= Math.cos(parseFloat(a[atr]) * tpGS.DEG2RAD);
			}
			if (atr==="skewY" && a[atr]!==undefined & parseFloat(a[atr])!==0) {
				a['scaleX'] = a['scaleX']===undefined ? 1 : parseFloat(a['scaleX']);
				a['scaleX'] *= Math.cos(parseFloat(a[atr]) * tpGS.DEG2RAD);
			}

		}

		a.transformOrigin = torig.originX+" "+torig.originY+" "+torig.originZ;

		// CLIPPING EFFECTS
		if (a.clip && RVS.L[_.layerid].timeline.clipPath.use) {
			var	cty = RVS.L[_.layerid].timeline.clipPath.type=="rectangle",
				cl = parseInt(a.clip,0),
				clb = 100-parseInt(a.clipB,0),
				ch = Math.round(cl/2);

			switch (RVS.L[_.layerid].timeline.clipPath.origin) {
				case "invh": a.clipPath = "polygon(0% 0%, 0% 100%, "+cl+"% 100%, "+cl+"% 0%, 100% 0%, 100% 100%, "+clb+"% 100%, "+clb+"% 0%, 0% 0%)";break;
				case "invv": a.clipPath = "polygon(100% 0%, 0% 0%, 0% "+cl+"%, 100% "+cl+"%, 100% 100%, 0% 100%, 0% "+clb+"%, 100% "+clb+"%, 100% 0%)";break;
				case "cv":a.clipPath = cty ? "polygon("+(50-ch)+"% 0%, "+(50+ch)+"% 0%, "+(50+ch)+"% 100%, "+(50-ch)+"% 100%)" : "circle("+cl+"% at 50% 50%)";break;
				case "ch":a.clipPath = cty ? "polygon(0% "+(50-ch)+"%, 0% "+(50+ch)+"%, 100% "+(50+ch)+"%, 100% "+(50-ch)+"%)" : "circle("+cl+"% at 50% 50%)";break;
				case "l":a.clipPath = cty ? "polygon(0% 0%, "+cl+"% 0%, "+cl+"% 100%, 0% 100%)" : "circle("+cl+"% at 0% 50%)";break;
				case "r":a.clipPath = cty ? "polygon("+(100-cl)+"% 0%, 100% 0%, 100% 100%, "+(100-cl)+"% 100%)" : "circle("+cl+"% at 100% 50%)";break;
				case "t":a.clipPath = cty ? "polygon(0% 0%, 100% 0%, 100% "+cl+"%, 0% "+cl+"%)" : "circle("+cl+"% at 50% 0%)";break;
				case "b":a.clipPath = cty ? "polygon(0% 100%, 100% 100%, 100% "+(100-cl)+"%, 0% "+(100-cl)+"%)" : "circle("+cl+"% at 50% 100%)";break;
				case "lt":a.clipPath = cty ? "polygon(0% 0%,"+(2*cl)+"% 0%, 0% "+(2*cl)+"%)" : "circle("+cl+"% at 0% 0%)";break;
				case "lb":a.clipPath = cty ? "polygon(0% "+(100 - 2*cl)+"%, 0% 100%,"+(2*cl)+"% 100%)" : "circle("+cl+"% at 0% 100%)";break;
				case "rt":a.clipPath = cty ? "polygon("+(100-2*cl)+"% 0%, 100% 0%, 100% "+(2*cl)+"%)" : "circle("+cl+"% at 100% 0%)";break;
				case "rb":a.clipPath = cty ? "polygon("+(100-2*cl)+"% 100%, 100% 100%, 100% "+(100 - 2*cl)+"%)" : "circle("+cl+"% at 100% 100%)";break;
				case "clr":a.clipPath = cty ? "polygon(0% 0%, 0% "+cl+"%, "+(100-cl)+"% 100%, 100% 100%, 100% "+(100-cl)+"%, "+cl+"% 0%)" : "circle("+cl+"% at 50% 50%)";break;
				case "crl":a.clipPath = cty ? "polygon(0% "+(100-cl)+"%, 0% 100%, "+cl+"% 100%, 100% "+cl+"%, 100% 0%, "+(100-cl)+"% 0%)" : "circle("+cl+"% at 50% 50%)";break;
			}
			if (RVS.F.isFirefox()!==true) a["-webkit-clip-path"] = a.clipPath;
			a["clip-path"] = a.clipPath;
			//a.overflow = "hidden"
			delete a.clip;
		} else
		if (a.clip) {
			a.clipPath = RVS.L[_.layerid].idle.spikeUse ? "polygon("+RVS.F.getClipPaths(RVS.L[_.layerid].idle.spikeLeft,0,parseFloat(RVS.L[_.layerid].idle.spikeLeftWidth))+","+RVS.F.getClipPaths(RVS.L[_.layerid].idle.spikeRight,100,(100-parseFloat(RVS.L[_.layerid].idle.spikeRightWidth)),true)+")" : "none";
			if (RVS.F.isFirefox()!==true)  a["-webkit-clip-path"] = a.clipPath;
			a["clip-path"] = a.clipPath;
			//if (a.clipPath!=="none") a.overflow = "hidden"
			delete a.clip;
		}



		// FILTER EFFECTS
		if (_.frame!==undefined && _.frame.filter!==undefined && _.frame.filter.use) {
			a['-webkit-filter']  = 'blur('+(parseInt(_.frame.filter.blur,0) || 0)+'px) grayscale('+(parseInt(_.frame.filter.grayscale,0) || 0)+'%) brightness('+(parseInt(_.frame.filter.brightness,0) || 100)+'%)';
			a.filter = 'blur('+(parseInt(_.frame.filter.blur,0) || 0)+'px) grayscale('+(parseInt(_.frame.filter.grayscale,0) || 0)+'%) brightness('+(parseInt(_.frame.filter.brightness,0) || 100)+'%)';
		} else
		if (jQuery.inArray(_.source,["chars","words","lines"])>=0 && _.frame[_.source].fuse) {
			a['-webkit-filter']  = 'blur('+(parseInt(_.frame[_.source].blur,0) || 0)+'px) grayscale('+(parseInt(_.frame[_.source].grayscale,0) || 0)+'%) brightness('+(parseInt(_.frame[_.source].brightness,0) || 100)+'%)';
			a.filter = 'blur('+(parseInt(_.frame[_.source].blur,0) || 0)+'px) grayscale('+(parseInt(_.frame[_.source].grayscale,0) || 0)+'%) brightness('+(parseInt(_.frame[_.source].brightness,0) || 100)+'%)';
		} else {
			if (_.sessionFilterUsed || _.sessionFilterUsed===undefined){
				a['-webkit-filter'] = "blur(0px) grayscale(0%) brightness(100%)";
				a.filter = "blur(0px) grayscale(0%) brightness(100%)";
			} else {
				a['-webkit-filter'] = "none";
				a.filter = "none";
			}
		}

		// EASE
		a.ease = a.ease!==undefined ? a.ease : (a.ease===undefined && _.ease!==undefined) || (a.ease!==undefined && _.ease !==undefined && a.ease==="inherit") ? _.ease : _.frame.timeline.ease;
		a.ease = a.ease===undefined || a.ease==="default" ? "power3.inOut" : a.ease;
		a.force3D = "auto";
		return a;
	}

	RVS.F.checkGlobalFiltersOnLayer = function(lid) {
		var gf = RVS.L[lid].timeline.hoverFilterUsed===true;
		if (gf!==true) for (var f in RVS.L[lid].timeline.frames) if (gf===true || !RVS.L[lid].timeline.frames.hasOwnProperty(f)) continue;	 else gf = RVS.L[lid].timeline.frames[f].filter.use;
		return gf;
	}

	RVS.F.getClipPaths = function(_,o,i,reverse) {
		var r;
		switch (_) {
			case "none" :   r=o+'% 100%,'+o+'% 0%';break;
			case "top" :    r=i+'% 100%,'+o+'% 0%'; break;
			case "middle" : r=i+'% 100%,'+o+'% 50%,'+i+'% 0%'; break;
			case "bottom" : r=o+'% 100%,'+i+'% 0%'; break;
			case "two": 	r=i+'% 100%,'+o+'% 75%,'+i+'% 50%,'+o+'% 25%,'+i+'% 0%';break;
			case "three": 	r=o+'% 100%,'+i+'% 75%,'+o+'% 50%,'+i+'% 25%,'+o+'% 0%';break;
			case "four": 	r=o+'% 100%,'+i+'% 87.5%,'+o+'% 75%,'+i+'% 62.5%,'+o+'% 50%,'+i+'% 37.5%,'+o+'% 25%,'+i+'% 12.5%,'+o+'% 0%';break;
			case "five": 	r=o+'% 100%,'+i+'% 90%,'+o+'% 80%,'+i+'% 70%,'+o+'% 60%,'+i+'% 50%,'+o+'% 40%,'+i+'% 30%,'+o+'% 20%,'+i+'% 10%,'+o+'% 0%';break;
		}
		if (reverse) {
			var s = r.split(",");
			r="";
			for (var i in s) {
				if(!s.hasOwnProperty(i)) continue;
				r+=s[(s.length-1)-i]+(i<s.length-1 ? "," : "");
			}
		}
		return r;
	};

	/*


.cornerdemo.corner_four { clip-path: polygon(0% 100%, 10% 90%, 0% 70%, 10% 50%, 0% 30%, 10% 10%, 0% 0%, 100% 0%, 90% 10%, 100% 30%, 90% 50%, 100% 70%, 90% 90%, 100% 100%);}
.cornerdemo.corner_five { clip-path: polygon(0% 0%, 100% 0%, 90% 10%, 100% 20%, 90% 30%, 100% 40%, 90% 50%, 100% 60%, 90% 70%, 100% 80%, 90% 90%, 100% 100%, 0% 100%, 10% 90%, 0% 80%, 10% 70%, 0% 60%, 10% 50%, 0% 40%, 10% 30%, 0% 20%, 10% 10%);}
*/



	/*********************************
		-	TIMELINE FUNCTIONS -
	**********************************/

	RVS.F.toggleTimeLine = function() {
		if (RVS.TL.timelineStartedFromPlayStop)
			RVS.DOC.trigger('stopTimeLine');
		else
			RVS.DOC.trigger('playTimeLine');
	};
	/*
	INIT CUSTOM EVENT LISTENERS FOR TRIGGERING FUNCTIONS
	*/
	function initLocalListeners() {
		// DEFAULTS
		RVS.ENV.tlGridWrap = RVS.ENV.tlGridWrap===undefined ? jQuery('#tl_gridmanagement_wrap') : RVS.ENV.tlGridWrap;
		RVS.ENV.tlMultipWrap = RVS.ENV.tlMultipWrap===undefined ? jQuery('.tl_multip_wrap') : RVS.ENV.tlMultipWrap;
		RVS.ENV.tlMagnifWrap = RVS.ENV.tlMagnifWrap===undefined ? jQuery('.tl_magnifying_wrap') : RVS.ENV.tlMagnifWrap;



		// UPDATE SLIDE LOOP RANGE

		RVS.DOC.on('click','#maxtime',function() {
			jQuery('.slide_submodule_trigger.selected').removeClass("selected");
			RVS.F.mainMode({mode:"slidelayout", forms:["*slidelayout**mode__slidestyle*#form_slide_progress"], set:true, uncollapse:true,slide:RVS.S.slideId});
		});

		RVS.DOC.on('updateAllLayerFrames',RVS.F.updateAllLayerFrames);

		RVS.DOC.on('updateSlideLoopRange',function() {
			if (RVS.SLIDER[RVS.S.slideId].slide.timeline.loop.set) RVS.F.updateSlideLoopTimes({cont:true, pos:true, start:RVS.SLIDER[RVS.S.slideId].slide.timeline.loop.start/10, end:RVS.SLIDER[RVS.S.slideId].slide.timeline.loop.end/10});
		});

		RVS.DOC.on('updateFixedScrollRange',function() {
			if (RVS.SLIDER.settings.scrolltimeline.set && RVS.SLIDER.settings.scrolltimeline.fixed) RVS.F.updateFixedScrollTimes({cont:true, pos:true, start:parseInt(RVS.SLIDER.settings.scrolltimeline.fixedStart)/10, end:parseInt(RVS.SLIDER.settings.scrolltimeline.fixedEnd)/10});
		});

		//SHORTLINK TO SLIDE ANIM
		RVS.DOC.on('click','#the_slide_timeline' , function() {
			RVS.F.selectLayers({overwrite:true});
			jQuery('.slide_submodule_trigger.selected').removeClass("selected");
			RVS.F.mainMode({mode:"slidelayout", forms:["*slidelayout**mode__slidestyle*#form_slide_transition"], set:true, uncollapse:true,slide:RVS.S.slideId});
			return false;
		});



		// SPEED MANIPULATION
		RVS.DOC.on('click','#tl_multiplicator',function() {
			RVS.ENV.tlMultipWrap.toggleClass("selected");
			if (RVS.ENV.tlMultipWrap.hasClass("selected")) {
				RVS.ENV.tlMagnifWrap.removeClass("selected");
				RVS.ENV.tlGridWrap.removeClass("selected");
				RVS.F.clearSnapVisual();
			}
		});

		RVS.DOC.on('click','#tl_framemagnet',function() {
			RVS.ENV.tlMagnifWrap.toggleClass("selected");
			if (RVS.ENV.tlMagnifWrap.hasClass("selected")) {
				RVS.ENV.tlMultipWrap.removeClass("selected");
				RVS.ENV.tlGridWrap.removeClass("selected");
				RVS.F.clearSnapVisual();
			}
		});

		RVS.DOC.on('click','#tl_gridmanagement',function() {
			RVS.F.updateEasyInputs({container:RVS.ENV.tlGridWrap,visualUpdate:true});
			RVS.ENV.tlGridWrap.toggleClass("selected");
			if (RVS.ENV.tlGridWrap.hasClass("selected")) {
				RVS.F.snapVisual();
				RVS.ENV.tlMagnifWrap.removeClass("selected");
				RVS.ENV.tlMultipWrap.removeClass("selected");
			} else {
				RVS.F.clearSnapVisual();
			}
		});

		RVS.DOC.on('click','.closeme_tl_miniwrapper',function() {
			RVS.ENV.tlGridWrap.removeClass("selected");
			RVS.ENV.tlMagnifWrap.removeClass("selected");
			RVS.ENV.tlMultipWrap.removeClass("selected");
		});

		RVS.DOC.on('magnetframes',function(e,param) {
			if (param!==undefined && param.val!==undefined) frameMagnify = param.val;
		});

		RVS.DOC.on('click','#gsf_ok',function() {
			var ns = parseInt(document.getElementById('general_speed_factor').value,0);
			if (jQuery.isNumeric(ns) && ns!==100) {
				ns = ns / 100;
				RVS.F.openBackupGroup({id:"frame",txt:"General Timings",icon:"access_time"});
				for (var li in RVS.L) if (RVS.L.hasOwnProperty(li)) {
					if (RVS.L[li].timeline!==undefined)
						for (var fi in RVS.L[li].timeline.frames) {
							if(!RVS.L[li].timeline.frames.hasOwnProperty(fi)) continue;
							let frame = RVS.L[li].timeline.frames[fi];
							if (jQuery.isNumeric(parseInt(frame.timeline.start,0))) RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+li+".timeline.frames."+fi+".timeline.start",val:Math.round(parseInt(frame.timeline.start,0) * ns)});
							if (jQuery.isNumeric(parseInt(frame.timeline.speed,0))) RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+li+".timeline.frames."+fi+".timeline.speed",val:Math.round(parseInt(frame.timeline.speed,0) * ns)});
							if (frame.words.use && jQuery.isNumeric(parseInt(frame.words.delay,0))) RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+li+".timeline.frames."+fi+".words.delay",val:Math.round(parseInt(frame.words.delay,0) * ns)});
							if (frame.chars.use && jQuery.isNumeric(parseInt(frame.chars.delay,0))) RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+li+".timeline.frames."+fi+".chars.delay",val:Math.round(parseInt(frame.chars.delay,0) * ns)});
							if (frame.lines.use && jQuery.isNumeric(parseInt(frame.lines.delay,0))) RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+li+".timeline.frames."+fi+".lines.delay",val:Math.round(parseInt(frame.lines.delay,0) * ns)});
						}
				}

				// UPDATE END TIME
				RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.timeline.delay",val:(Math.round(RVS.F.getSlideLength() * ns)) * 10});
				if (jQuery.isNumeric(RVS.SLIDER[RVS.S.slideId].slide.timeline.duration[0])) RVS.F.updateSliderObj({path:RVS.S.slideId+".slide.timeline.duration.0",val:(Math.round(RVS.SLIDER[RVS.S.slideId].slide.timeline.duration[0] * ns))});
				RVS.F.updateAllLayerFrames();
				RVS.F.updateSlideFrames();
				RVS.F.closeBackupGroup({id:"frame"});
				RVS.DOC.trigger('updateMaxTime');
			}
			document.getElementById('general_speed_factor').value = "100%";
		});

		RVS.DOC.on('updateMaxTime',function(e,ep) {
			RVS.F.updateMaxTime({pos:true, cont:true});
			var _ = RVS.F.getLayersEndWithSlide(),
				slideMaxTime = RVS.F.getSlideLength();

			for (var li in _) {
				if(!_.hasOwnProperty(li)) continue;
				if (_[li].endWithSlide) {
					RVS.L[li].timeline.frames.frame_999.timeline.start = slideMaxTime*10;
					RVS.F.updateLayerFrame({layerid:li, frame:"frame_999",maxtime:slideMaxTime});
				}
			}
		});

		RVS.DOC.on('windowresized',function() {

			// bounce if window is resized when editor is first loading
			if(!RVS.TL.hasOwnProperty('cache')) return;

			/* var currentstate = 0; */
			RVS.TL.timelineStartedFromPlayStop = false;
			RVS.TL.cache.main = 0;
			RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
			RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
		});

		RVS.DOC.on('updateSlideTransitionTimeLine',function() {
			RVS.F.updateSlideFrames();
		});

		RVS.DOC.on('playTimeLine',function() {

			RVS.F.changeSwitchState({el:document.getElementById("timline_process"),state:"stop"});
			RVS.TL.timelineStartedFromPlayStop=true;
			RVS.F.buildMainTimeLine();
			var currentstate = (RVS.TL[RVS.S.slideId] && RVS.TL[RVS.S.slideId].main) ? RVS.TL[RVS.S.slideId].main.time() : 0;
			RVS.F.updateTimeLine({force:true, state:"time",time:currentstate, timeline:"main", forceFullLayerRender:true, updateCurTime:true});
			RVS.F.updateTimeLine({state:"play",timeline:"main", force:false});
		});
		RVS.DOC.on('stopTimeLine',function() {

			/* var currentstate = 0; */
			RVS.TL.cache.main = 0;
			RVS.TL.timelineStartedFromPlayStop = false;
			RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
			RVS.F.buildMainTimeLine();
			RVS.F.updateCurTime({pos:true, cont:true, force:true, left:0,refreshMainTimeLine:true, caller:"GoToIdle"});
		});

		RVS.DOC.on('previewLayerAnimation',function() {
			RVS.S.shwLayerAnim = true;
			RVS.F.changeSwitchState({el:document.getElementById("layer_simulator"),state:"stop"});
			RVS.F.changeSwitchState({el:document.getElementById("layer_simulator_loop"),state:"stop"});
			for (var lid in RVS.selLayers) {
				if(!RVS.selLayers.hasOwnProperty(lid)) continue;
				RVS.F.renderLayerAnimation({layerid:RVS.selLayers[lid]});
				RVS.F.playLayerAnimation({layerid:RVS.selLayers[lid]});
			}
		});

		RVS.DOC.on('previewStopLayerAnimation',function() {
			RVS.S.shwLayerAnim = false;
			RVS.F.changeSwitchState({el:document.getElementById("layer_simulator"),state:"play"});
			RVS.F.changeSwitchState({el:document.getElementById("layer_simulator_loop"),state:"play"});
			RVS.F.stopAllLayerAnimation();
		});


		RVS.DOC.on('click','#copy_keyframe',function() {
			if (RVS.selLayers.length==1 && RVS.S.keyFrame!==undefined) {
				keyframecache = RVS.F.safeExtend(true,{},RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame]);
				jQuery('#paste_keyframe').show();
			}

		});

		RVS.DOC.on('click','#paste_keyframe',function() {
			if (RVS.selLayers.length==1 && RVS.S.keyFrame!==undefined) {
				var fr = RVS.F.getPrevNextFrame({layerid:RVS.selLayers[0], frame:RVS.S.keyFrame});
				if (fr.next.start>=fr.cur.end+keyframecache.timeline.frameLength) {
					var cur = RVS.L[RVS.selLayers[0]].timeline.frames[RVS.S.keyFrame];
					keyframecache.timeline.actionTriggered = cur.timeline.actionTriggered;
					keyframecache.timeline.start = cur.timeline.start;
					keyframecache.timeline.startRelative = cur.timeline.startRelative;
					RVS.F.updateSliderObj({path:RVS.S.slideId+".layers."+RVS.selLayers[0]+".timeline.frames."+RVS.S.keyFrame,val:keyframecache});
					RVS.DOC.trigger('updateKeyFramesList');
					RVS.F.updateAllLayerFrames();
					RVS.F.updateInputFields();
				} else {
					RVS.F.showInfo({content:RVS_LANG.framesizecannotbeextended, type:"warning", showdelay:0, hidedelay:2, hideon:"", event:"" });
				}
			}
		});


	}

	/*
	Get Layer With last Start Point
	*/
	/*
	function lastStart() {
		var ret = 0;
		for (var li in RVS.L) {
			if(!RVS.L.hasOwnProperty(li)) continue;
			var c = RVS.L[li].timeline.frames.frame_999.timeline.start;
			ret = ret <  c ? c : ret;
		}
		return ret;
	}
	*/

	/*
	Get Layer Before FRAME_999 Wiht Biggest End Point
	*/
	function beforeLastEnd() {
		var ret = 0;
		for (var li in RVS.L) {
			if(!RVS.L.hasOwnProperty(li)) continue;
			if (li!=="top" && li!=="bottom" && li!=="middle") {
				var pn = RVS.F.getPrevNextFrame({layerid:li, frame:"frame_999"});
				ret = ret <  pn.prev.end ? pn.prev.end : ret;
			}
		}
		return ret;
	}



	function buildRuler() {
		var a=0;
		for(var i=0;i<2000;i++) {
			if (a%20===0)
				tlr.append('<div class="rm_twosec" style="left:'+(i*10)+'px"><span class="rulertxt">'+(i/10)+'s</span></div>');
			else
			if (a%10===0)
				tlr.append('<div class="rm_sec" style="left:'+(i*10)+'px"><span class="rulertxt">'+(i/10)+'s</span></div>');
			else
				tlr.append('<div class="rm_ms" style="left:'+(i*10)+'px"></div>');


			a++;
			a = a==20 ? 0 : a;
		}
	}



	function pxToSec(d) {

		var min = Math.floor(d/6000),
			sec = Math.floor(Math.ceil(d - (min*6000))/100),
			ms = Math.round(d-(sec*100)-(min*6000));

		if (min==0) min = "00";
		else
		if (min<10) min = "0"+min.toString();

		if (sec==0) sec = "00";
		else
		if (sec<10) sec = "0"+sec.toString();

		if (ms==0) ms = "00";
		else
		if (ms<10) ms = "0"+ms.toString();
		return {m:min.toString(), s:sec.toString(), ms:ms.toString()};
	}

	//////////////////////////////
	//	SWAP SLIDE PROGRESS		//
	//////////////////////////////


	RVS.F.getSliderTransitionParameters = function(reqTrans,SDIR) {

		//MAX 60
		var
			p1i = "power1.in",
			p1o = "power1.out",
			p1io = "power1.inOut",
			p2i = "power2.in",
			p2o = "power2.out",
			p2io = "power2.inOut",
			/* p3i = "power3.in", */
			p3o = "power3.out",
			p3io = "power3.inOut",
			flatT = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45],
			premT = [17,18,19,20,21,22,23,24,25,27],
			nTR =0,
			trC = 1,
			TRindex = 0,
			/* TR = new Array, */
			tA = [ 				 ['boxslide' , 0, 0, 10, 'box',false,null,0,p1o,p1o,1000,6],
								 ['boxrandomrotate' , 0, 1, 10, 'box',false,null,60,p1o,p1o,1000,6],
								 ['boxfade', 1, 0, 10, 'box',false,null,1,p1io,p1io,700,5],
								 ['slotslide-horizontal', 2, 0, 0, 'horizontal',true,false,2,p2io,p2io,1000,3],
								 ['slotslide-vertical', 3, 0,0,'vertical',true,false,3,p2io,p2io,1000,3],
								 ['curtain-1', 4, 3,0,'horizontal',true,true,4,p1o,p1o,900,5],
								 ['curtain-2', 5, 3,0,'horizontal',true,true,5,p1o,p1o,900,5],
								 ['curtain-3', 6, 3,25,'horizontal',true,true,6,p1o,p1o,900,5],
								 ['slotzoom-horizontal', 7, 0,0,'horizontal',true,true,7,p1o,p1o,1000,7],
								 ['slotzoom-vertical', 8, 0,0,'vertical',true,true,8,p2o,p2o,1000,8],
								 ['slotzoom-mixed', 8, 1,0,'vertical',true,true,59,p2o,p2o,1000,8],
								 ['slotfade-horizontal', 9, 0,0,'horizontal',true,null,9,p2io,p2io,1500,10],
								 ['slotfade-vertical', 10, 0,0 ,'vertical',true,null,10,p2io,p2io,1500,10],
								 ['crossfade-horizontal', 9, 0,0,'horizontal',true,null,9,p2io,p2io,0,10],
								 ['crossfade-vertical', 10, 0,0 ,'vertical',true,null,10,p2io,p2io,0,10],
								 ['fade', 11, 0, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['crossfade', 11, 1, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['fadethroughdark', 11, 2, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['fadethroughlight', 11, 3, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['fadethroughtransparent', 11, 4, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['slideleft', 12, 0,1,'horizontal',true,true,12,p3io,p3io,1000,1],
								 ['slideup', 13, 0,1,'horizontal',true,true,13,p3io,p3io,1000,1],
								 ['slidedown', 14, 0,1,'horizontal',true,true,14,p3io,p3io,1000,1],
								 ['slideright', 15, 0,1,'horizontal',true,true,15,p3io,p3io,1000,1],
								 ['slideoverleft', 12, 7,1,'horizontal',true,true,12,p3io,p3io,1000,1],
								 ['slideoverup', 13, 7,1,'horizontal',true,true,13,p3io,p3io,1000,1],
								 ['slideoverdown', 14, 7,1,'horizontal',true,true,14,p3io,p3io,1000,1],
								 ['slideoverright', 15, 7,1,'horizontal',true,true,15,p3io,p3io,1000,1],
								 ['slideremoveleft', 12, 8,1,'horizontal',true,true,12,p3io,p3io,1000,1],
								 ['slideremoveup', 13, 8,1,'horizontal',true,true,13,p3io,p3io,1000,1],
								 ['slideremovedown', 14, 8,1,'horizontal',true,true,14,p3io,p3io,1000,1],
								 ['slideremoveright', 15, 8,1,'horizontal',true,true,15,p3io,p3io,1000,1],
								 ['papercut', 16, 0,0,'vertical',null,true,16,p3io,p3io,1000,2],
								 ['3dcurtain-horizontal', 17, 0,20,'vertical',true,true,17,p1io,p1io,2000,7],
								 ['3dcurtain-vertical', 18, 0,10,'horizontal',true,true,18,p1io,p1io,2000,7],
								 ['cubic', 19, 0,20,'horizontal',false,true,19,p2io,p2io,1000,1],
								 ['cube',19,0,20,'horizontal',false,true,20,p2io,p2io,1000,1],
								 ['flyin', 20, 0,4,'vertical',false,true,21,p3o,p3io,1000,1],
								 ['turnoff', 21, 0,1,'horizontal',false,true,22,p3io,p3io,1000,1],
								 ['incube', 22, 0,20,'horizontal',false,true,23,p2io,p2io,1000,1],
								 ['cubic-horizontal', 23, 0,20,'vertical',false,true,24,p2io,p2io,1000,1],
								 ['cube-horizontal', 23, 0,20,'vertical',false,true,25,p2io,p2io,1000,1],
								 ['incube-horizontal', 24, 0,20,'vertical',false,true,26,p2io,p2io,1000,1],
								 ['turnoff-vertical', 25, 0,1,'horizontal',false,true,27,p2io,p2io,1000,1],
								 ['fadefromright', 12, 1,1,'horizontal',true,true,28,p2io,p2io,1000,1],
								 ['fadefromleft', 15, 1,1,'horizontal',true,true,29,p2io,p2io,1000,1],
								 ['fadefromtop', 14, 1,1,'horizontal',true,true,30,p2io,p2io,1000,1],
								 ['fadefrombottom', 13, 1,1,'horizontal',true,true,31,p2io,p2io,1000,1],
								 ['fadetoleftfadefromright', 12, 2,1,'horizontal',true,true,32,p2io,p2io,1000,1],
								 ['fadetorightfadefromleft', 15, 2,1,'horizontal',true,true,33,p2io,p2io,1000,1],
								 ['fadetobottomfadefromtop', 14, 2,1,'horizontal',true,true,34,p2io,p2io,1000,1],
								 ['fadetotopfadefrombottom', 13, 2,1,'horizontal',true,true,35,p2io,p2io,1000,1],
								 ['parallaxtoright', 15, 3,1,'horizontal',true,true,36,p2io,p2io,1500,1],
								 ['parallaxtoleft', 12, 3,1,'horizontal',true,true,37,p2io,p2io,1500,1],
								 ['parallaxtotop', 14, 3,1,'horizontal',true,true,38,p2io,p2io,1500,1],
								 ['parallaxtobottom', 13, 3,1,'horizontal',true,true,39,p2io,p2io,1500,1],
								 ['scaledownfromright', 12, 4,1,'horizontal',true,true,40,p2io,p2i,1000,1],
								 ['scaledownfromleft', 15, 4,1,'horizontal',true,true,41,p2io,p2i,1000,1],
								 ['scaledownfromtop', 14, 4,1,'horizontal',true,true,42,p2io,p2i,1000,1],
								 ['scaledownfrombottom', 13, 4,1,'horizontal',true,true,43,p2io,p2i,1000,1],
								 ['zoomout', 13, 5,1,'horizontal',true,true,44,p2io,p2io,1000,1],
								 ['zoomin', 13, 6,1,'horizontal',true,true,45,p2io,p2io,1000,1],
								 ['slidingoverlayup', 27, 0,1,'horizontal',true,true,47,p1io,p1o,2000,1],
								 ['slidingoverlaydown', 28, 0,1,'horizontal',true,true,48,p1io,p1o,2000,1],
								 ['slidingoverlayright', 30, 0,1,'horizontal',true,true,49,p1io,p1o,2000,1],
								 ['slidingoverlayleft', 29, 0,1,'horizontal',true,true,50,p1io,p1o,2000,1],
								 ['parallaxcirclesup', 31, 0,1,'horizontal',true,true,51,p2io,p1i,1500,1],
								 ['parallaxcirclesdown', 32, 0,1,'horizontal',true,true,52,p2io,p1i,1500,1],
								 ['parallaxcirclesright', 33, 0,1,'horizontal',true,true,53,p2io,p1i,1500,1],
								 ['parallaxcirclesleft', 34, 0,1,'horizontal',true,true,54,p2io,p1i,1500,1],
								 ['notransition',26,0,1,'horizontal',true,null,46,p2io,p2i,1000,1],
								 ['parallaxright', 15, 3,1,'horizontal',true,true,55,p2io,p2i,1500,1],
								 ['parallaxleft', 12, 3,1,'horizontal',true,true,56,p2io,p2i,1500,1],
								 ['parallaxup', 14, 3,1,'horizontal',true,true,57,p2io,p1i,1500,1],
								 ['parallaxdown', 13, 3,1,'horizontal',true,true,58,p2io,p1i,1500,1],
								 ['grayscale', 11, 5, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['grayscalecross', 11, 6, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['brightness', 11, 7, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['brightnesscross', 11, 8, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['blurlight', 11, 9, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['blurlightcross', 11, 10, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['blurstrong', 11, 9, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
								 ['blurstrongcross', 11, 10, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1]
							   ];

		// CHECK AUTO DIRECTION FOR TRANSITION ARTS
		jQuery.each(["parallaxcircles","slidingoverlay","slide","slideover","slideremove","parallax","parralaxto"],function(i,b) {
			if (reqTrans==b+"horizontal")  reqTrans = SDIR!=1 ? b+"left" : b+"right";
			if (reqTrans==b+"vertical") reqTrans = SDIR!=1 ? b+"up" : b+"down";
		});

		// RANDOM TRANSITIONS
		if (reqTrans == "random") reqTrans = Math.min(Math.round(Math.random()*tA.length-1),tA.length-1);
		else
		if (reqTrans == "random-static") reqTrans = flatT[Math.min(Math.round(Math.random()*flatT.length-1),flatT.length-1)];
		else
		if (reqTrans == "random-premium") reqTrans = premT[Math.min(Math.round(Math.random()*premT.length-1),premT.length-1)];

		// FIND THE RIGHT TRANSITION PARAMETERS
		jQuery.each(tA,function(inde,trans) {
			if (trans[0] == reqTrans || trans[7] == reqTrans) {
				nTR = trans[1];
				trC = trans[2];
				TRindex = inde; //indexcounter;
			}
		});

		nTR = Math.max(0,Math.min(30,nTR));
		return {nTR:nTR, TR: tA[TRindex], trC:trC};
	};



	var interSlideAnimation = function(CUR,PREV,ANIM,MS) {

		// GET THE TRANSITION
		var SDIR = 0, //1 ??
			/* container = {}, */
			opt = {width:RVS.C.slide.width(), height:/*RVS.C.slide.height()*/RVS.S.ulDIM.height-65, mtl:new tpGS.TimelineMax()},
			_ = RVS.F.getSliderTransitionParameters(ANIM,SDIR),
			nTR = RVS.SLIDER[RVS.S.slideId].slide.panzoom.set && (_.nTR<11 || _.nTR==17 || _.nTR===18 || (_.nTR>=27 && _.nTR<=30)) ? 11 : _.nTR;


		MS= MS===undefined ? RVS.F.getSlideAnimParams("duration") : "default";



		//	ADJUST SETTINGS
		opt.slots = RVS.F.getSlideAnimParams("slots");
		opt.rotate = RVS.F.getSlideAnimParams("rotation");



		// ADJUST MASTERSPEED
		MS = MS==="default" || MS==="d" ? _.TR[10] : MS==="random" ? Math.round(Math.random()*1000+300) : MS!=undefined ? parseInt(MS,0) : _.TR[10];
		opt.rotate = opt.rotate==undefined || opt.rotate=="default" || opt.rotate=="d" ? 0 : opt.rotate==999 || opt.rotate=="random" ? Math.round(Math.random()*360) : opt.rotate;

		// PREPEARE ONE SLIDE IF SLOTS ARE IN GAME
		if (nTR<11 || nTR===16 || nTR===17 || nTR===18 || (_.nTR>=27 && _.nTR<=30)) {

			opt.slots = opt.slots==undefined || opt.slots=="default" || opt.slots=="d" ? _.TR[11] : opt.slots=="random" ? Math.round(Math.random()*12+4) : opt.slots;

			opt.slots = opt.slots < 1 ? _.TR[0]=="boxslide" ? Math.round(Math.random()*6+3) : _.TR[0]=="boxslide" || _.TR[0]=="flyin" ? Math.round(Math.random()*4+1) : opt.slots : opt.slots;

			opt.slots = (nTR==4 || nTR==5 || nTR==6) && opt.slots<3 ? 3 : opt.slots;

			opt.slots = _.TR[3] != 0 ? Math.min(opt.slots,_.TR[3]) : opt.slots;

			opt.slots = opt.slots===0 ? 5 : opt.slots;
			opt.slots = nTR==9 ? opt.width/opt.slots : nTR==10 ? opt.height/opt.slots : opt.slots;

			opt.slots = jQuery.inArray(nTR,[19,20,21,22,23,24,25,27])>=0 ? 1 : opt.slots;

			opt.slots = (nTR==3 || nTR==8 || nTR==10) && _.TR[4]==="vertical" ? opt.slots+2 : opt.slots;

			if (!jQuery.isNumeric(opt.slots)) opt.slots = 5;
			if (_.TR[6] !=null) opt = prepareOneSlide(PREV,opt,_.TR[6],_.TR[4],0);
			if (_.TR[5] !=null) opt = prepareOneSlide(CUR,opt,_.TR[5],_.TR[4]);

		}
		var OA = nTR===7 || nTR===16 || nTR===8 || nTR===17 || nTR===18 ? 0 : 1,
			OB = nTR<11 || nTR===17 || nTR===18 ? 0 : 1;


		opt.mtl.add(tpGS.gsap.set(PREV.find('rs-sbg'),{x:0, y:0, z:0, rotationZ:0, rotationX:0, rotationY:0, scale:1, top:0, left:0, clearProps:"filter, transform", opacity:1}),0);
		opt.mtl.add(tpGS.gsap.set(CUR.find('rs-sbg'),{x:0, y:0, z:0, rotationZ:0, rotationX:0, rotationY:0, scale:1, top:0, left:0, clearProps:"filter, transform", opacity:1}),0);
		opt.mtl.add(tpGS.gsap.set(PREV.find('rs-sbg'),{opacity:OA}),0.001);
		opt.mtl.add(tpGS.gsap.set(CUR.find('rs-sbg'),{opacity:OB}),0.001);


		opt.mtl.add(tpGS.gsap.set(CUR,{zIndex:20,transformOrigin:"50% 50% 0", transformPerspective:0, scale:1,rotationX:0, rotationY:0, rotationZ:0, z:0,autoAlpha:1,top:0, left:0, x:0, y:0, clearProps:"filter, transform"}),0);
		opt.mtl.add(tpGS.gsap.set(PREV,{zIndex:10,transformOrigin:"50% 50% 0", transformPerspective:0, scale:1,rotationX:0, rotationY:0, rotationZ:0, z:0,autoAlpha:1,top:0, left:0, x:0, y:0,clearProps:"filter, transform"}),0);


		opt.mtl.add(tpGS.gsap.set(CUR.parent(),{perspective:1200, transformStyle:"flat", force3D:'auto', backgroundColor:"transparent"}),0);

		// GET IN/OUT EASINGS
		var ei= RVS.F.getSlideAnimParams("easeIn"),
			eo =RVS.F.getSlideAnimParams("easeOut");

		ei = ei==="default" || ei==="d" ? _.TR[8] || "power2.inOut" : ei || _.TR[8] || "power2.inOut";
		eo = eo==="default" || eo==="d"  ? _.TR[9] || "power2.inOut" : eo || _.TR[9] || "power2.inOut";




		///////////////////
		// SLOT TRANSITION
		///////////////////
		if (nTR==0) {
			var maxz = Math.ceil(opt.height/opt.sloth), curz = 0;
			CUR.find('.slotslide').each(function(j) {
				curz++;
				curz= curz===maxz ? 0 : curz;
				opt.rotate = _.trC===1 ? 45 : opt.rotate;
				opt.mtl.add(tpGS.gsap.from(this,(MS)/2000,{opacity:0,transformStyle:"flat", transformPerspective:600, scale:0,rotationZ:opt.rotate!==0 ? Math.random()*opt.rotate-(opt.rotate/2) : 0,force3D:'auto',ease:ei}),((j*10) + ((curz)*30))/3000);

			});
		} else

		//////////////////
		// SLOT TRANSITION
		//////////////////
		if (nTR==1) {
			// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
			CUR.find('.slotslide').each(function(j) {
				opt.mtl.add(tpGS.gsap.from(this,(Math.random()*MS+300)/1000,{autoAlpha:0, force3D:'auto',rotation:opt.rotate,ease:ei}),(Math.random()*500+200)/1000);
			});
		} else


		///////////////////
		// SLOT TRANSITION
		///////////////////
		if (nTR==2 || nTR==3) {
			// ALL OLD SLOTS SHOULD BE SLIDED TO THE RIGHT
			PREV.find('.slotslide').each(function() {
				opt.mtl.add(tpGS.gsap.to(this,MS/1000,{top:nTR===3 ? opt.sloth : 0,left:nTR===2 ? opt.slotw : 0,ease:ei, force3D:'auto',rotation:(0-opt.rotate)}),0);
			});
			// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
			CUR.find('.slotslide').each(function() {
				opt.mtl.add(tpGS.gsap.from(this,MS/1000,{top:nTR==3 ? SDIR===1 ? 0-opt.sloth : opt.sloth : 0 , left:nTR==2 ? SDIR===1 ? 0-opt.slotw : opt.slotw : 0, ease:ei, force3D:'auto',rotation:opt.rotate}),0);
			});
		} else


		/////////////////////////////////////
		// SLOT TRANSITION  //
		////////////////////////////////////
		if (nTR==4 || nTR==5 || nTR==6) {

			var	subtl = new tpGS.TimelineLite(),
				cspeed = MS/1000 - MS/1000/opt.slots;
			opt.slots -= opt.slots%2==1 ? 1 : 0;
			PREV.find('.slotslide').each(function(j) {
				var i = nTR!==6 ? j : j>opt.slots/2 ? opt.slots-j : j;
				subtl.add(tpGS.gsap.to(this,cspeed,{transformPerspective:600,force3D:"auto",top:SDIR!==1 ? opt.height : -opt.height,opacity:0.75,rotation:opt.rotate,ease:ei,
					delay: ((nTR!==5 ? i : opt.slots-i) *(cspeed / opt.slots)) / (nTR===6 ? 1.3 : 1)}),0);
				opt.mtl.add(subtl,0);
			});

			CUR.find('.slotslide').each(function(j) {
				var i = nTR!==6 ? j : j>opt.slots/2 ? opt.slots-j : j;
				subtl.add(tpGS.gsap.from(this,cspeed,{top:SDIR==1 ? opt.height : -opt.height,opacity:0.75,rotation:opt.rotate,force3D:"auto",ease:eo,
					delay: ((nTR!==5 ? i : opt.slots-i) *(cspeed / opt.slots)) / (nTR===6 ? 1.3 : 1)}),0);
				opt.mtl.add(subtl,0);
			});
		} else

		////////////////////////////////////
		// THE SLOTSZOOM - TRANSITION II. //
		////////////////////////////////////
		if (nTR==7 || nTR==8) {
			MS = Math.min(opt.duration || MS, MS);
			// ALL OLD SLOTS SHOULD BE SLIDED TO THE RIGHT
			PREV.find('.slotslide').each(function(j) {
				var i = j>opt.slots/2 ? opt.slots-j : j;
				opt.mtl.add(tpGS.gsap.to(this.getElementsByTagName("div"),MS/1000,{x:nTR===8 && _.trC===0 ? 0 : i*opt.slotw/3, y:nTR===8 && _.trC===0 ? i*opt.sloth/3 : 0, ease:ei,transformPerspective:600,force3D:'auto',filter:"blur(2px)", scale:1.2,opacity:0}),0);
			});
			CUR.find('.slotslide').each(function(j) {
				var i = j>opt.slots/2 ? opt.slots-j : j;
				opt.mtl.add(tpGS.gsap.fromTo(this.getElementsByTagName("div"),MS/1000,{x:nTR===8 && _.trC===0 ? 0 : 0-i*opt.slotw/3, y:nTR===8 && _.trC===0 ? 0-i*opt.sloth/3 : 0, filter:"blur(2px)", opacity:0, transformPerspective:600, scale:1.2},{x:0,y:0,ease:eo,force3D:'auto',scale:1,filter:"blur(0px)", opacity:1,rotation:0}),0);
			});
		} else

		////////////////////////////////////////
		// THE SLOTSFADE - TRANSITION III.   //
		//////////////////////////////////////
		if (nTR==9 || nTR==10) {
			// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
			var ss = CUR[0].getElementsByClassName("slotslide"),
				sk = MS - MS/1.8;
			for (var i=0;i<ss.length;i++)
				opt.mtl.add(tpGS.gsap.fromTo(ss[i],(MS - (i * (sk / opt.slots)))/1000,{opacity:0,force3D:'auto',transformPerspective:600},{opacity:1,ease:"none",delay:(i * (sk / opt.slots))/1000}),0);
		} else



		///////////////////////
		// CROSS ANIMATIONS //
		//////////////////////
		if (nTR==11) {
			_.trC = Math.min(12,_.trC);

			var bgcol = _.trC == 2 ? "#000000" : _.trC == 3 ? "#ffffff" : "transparent";
			switch (_.trC) {
				case 0:  opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/1000,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),0); break;
				case 1: // CROSSFADE
					opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/1000,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),0);
					opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/1000,{autoAlpha:1},{autoAlpha:0,force3D:"auto",ease:ei}),0);
				break;
				case 2: case 3: case 4:
					opt.mtl.add(tpGS.gsap.set(PREV.parent(),{backgroundColor:bgcol,force3D:"auto"}),0);
					opt.mtl.add(tpGS.gsap.set(CUR.parent(),{backgroundColor:"transparent",force3D:"auto"}),0);
					opt.mtl.add(tpGS.gsap.to(PREV,MS/2000,{autoAlpha:0,force3D:"auto",ease:ei}),0);
					opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/2000,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),MS/2000);
				break;
				case 5: // GRAYSCALE
				case 6: // GRAYSCALECROSS
				case 7: // BRIGHTNESS
				case 8: // BRIGHTNESSCROSS
				case 9: // BLUR LIGHT
				case 10: // BLUR LIGHT CROSS
				case 11: // BLUR STRONG
				case 12: // BLUR STRONG CROSS
					var _blur = jQuery.inArray(_.trC,[9,10])>=0 ? 5 : jQuery.inArray(_.trC,[11,12])>=0 ? 10 : 0,
						_gray = jQuery.inArray(_.trC,[5,6,7,8])>=0 ? 100 : 0,
						_bright = jQuery.inArray(_.trC,[7,8])>=0 ? 300 : 0,
						ff = "blur("+_blur+"px) grayscale("+_gray+"%) brightness("+_bright+"%)",
						ft = "blur(0px) grayscale(0%) brightness(100%)";
					opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/1000,{autoAlpha:0,filter:ff, "-webkit-filter":ff},{autoAlpha:1,filter:ft, "-webkit-filter":ft,force3D:"auto",ease:ei}),0);
					if (jQuery.inArray(_.trC,[6,8,10])>=0) opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/1000,{autoAlpha:1,filter:ft, "-webkit-filter":ft},{autoAlpha:0,force3D:"auto",ease:ei,filter:ff, "-webkit-filter":ff}),0);
				break;
			}
			opt.mtl.add(tpGS.gsap.set(CUR.find('rs-sbg'),{autoAlpha:1}),0);
			opt.mtl.add(tpGS.gsap.set(PREV.find('rs-sbg'),{autoAlpha:1}),0);
	    } else


		///////////////////////
		// CROSS ANIMATIONS //
		//////////////////////
		if (nTR==12 || nTR==13 || nTR==14 || nTR==15) {
			/* var ssn=CUR, */
			var spd = _.trC == 3 ? MS / 1300 : MS/1000,
				spd2 = MS/1000,
				oow = _.trC==5 || _.trC==6 ? 0 : opt.width,
				ooh = _.trC==5 || _.trC==6 ? 0 : opt.height,
				twx = nTR==12 ? oow : nTR == 15 ? 0-oow : 0,
				twy = nTR==13 ? ooh : nTR == 14 ? 0 - ooh : 0,
				op = _.trC == 1 || _.trC == 2 || _.trC == 5 || _.trC == 6 ? 0 : 1,
				scal = _.trC==4 || _.trC==5 ? 0.6 : _.trC==6 ? 1.4 : 1,
				fromscale = _.trC==5 ? 1.4 : _.trC==6 ? 0.6 : 1;

			if (_.trC==7 || _.trC==4) { oow = 0;ooh = 0;}

			if (_.trC==8) {
				opt.mtl.add(tpGS.gsap.set(PREV,{zIndex:20}),0);
				opt.mtl.add(tpGS.gsap.set(CUR,{zIndex:15}),0);
				opt.mtl.add(tpGS.gsap.to(CUR,0.01,{overflow:"hidden", left:0, top:0, x:0, y:0, scale:1, autoAlpha:1,rotation:0,overwrite:true, immediateRender:true, force3D:"auto"}),0);
			} else {
				opt.mtl.add(tpGS.gsap.set(PREV,{zIndex:15}),0);
				opt.mtl.add(tpGS.gsap.set(CUR,{zIndex:20}),0);
				opt.mtl.add(tpGS.gsap.from(CUR,spd,{left:twx, top:twy, overflow:"hidden", scale:fromscale, autoAlpha:op,rotation:opt.rotate,ease:ei,force3D:"auto"}),0);
			}

			if (_.trC!=1)
				switch (nTR) {
					case 12:opt.mtl.add(tpGS.gsap.to(PREV,spd2,{'left':(0-oow)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:opt.rotate,ease:eo}),0);break;
					case 15:opt.mtl.add(tpGS.gsap.to(PREV,spd2,{'left':(oow)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:opt.rotate,ease:eo}),0);break;
					case 13:opt.mtl.add(tpGS.gsap.to(PREV,spd2,{'top':(0-ooh)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:opt.rotate,ease:eo}),0);break;
					case 14:opt.mtl.add(tpGS.gsap.to(PREV,spd2,{'top':(ooh)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:opt.rotate,ease:eo}),0);break;
				}
		} else


		////////////////////////////////////////
		// THE SLOTSLIDE - TRANSITION XVII.  //
		///////////////////////////////////////
		if (nTR==17 || nTR==18) {
			// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
			CUR.find('.slotslide').each(function(j) {
				opt.mtl.add(tpGS.gsap.fromTo(this,((MS/opt.slots))/1000,
					{opacity:0,top:0, left:0 ,rotationY: nTR===17 ? 0 : 90,scale:1,rotationX:nTR===17 ? (-90) : 0 ,force3D:"auto",transformPerspective:600,transformOrigin:nTR===17 ? "top center" : "center left"},
					{opacity:1,top:0,left:0,rotationX:0,rotationY:0,force3D:"auto",ease:eo,delay:j*(((MS/opt.slots))/2000)}),0);

			});
			PREV.find('.slotslide').each(function(j) {
				opt.mtl.add(tpGS.gsap.fromTo(this,((MS/opt.slots))/1000,
					{opacity:1,rotationY: 0,scale:1,rotationX:0 ,force3D:"auto",transformPerspective:600,transformOrigin:nTR===17 ? "bottom center" : "center right"},
					{opacity:0,rotationX:nTR===17 ? (110) : 0,rotationY:nTR===17 ? 0 : (110), force3D:"auto",ease:ei,delay:j*(((MS/opt.slots))/2000)}),0);

			});
		} else

		////////////////////////////////////////
		// THE SLOTSLIDE - TRANSITION XIX.  //
		///////////////////////////////////////
		if (nTR==19 || nTR==22 || nTR==23 || nTR==24) {	// CUBE ANIMATIONS

			//SET DEFAULT IMG UNVISIBLE
			opt.mtl.add(tpGS.gsap.set(PREV,{zIndex:20}),0);
			opt.mtl.add(tpGS.gsap.set(CUR,{zIndex:10}),0);

			var torig = nTR===19 ? "center center -"+opt.height/2+"px" : nTR===22 ?  "center center "+opt.height/2+"px" : nTR===23 ? "center center -"+(opt.width/2)+"px" : "center center "+opt.width/2+"px";



			// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
			tpGS.gsap.set(jQuery('.slots_wrapper'),{transformStyle:"flat",backfaceVisibility:"hidden",force3D:true});
			opt.mtl.add(tpGS.gsap.set([CUR,PREV],{transformPerspective:1200,transformOrigin:torig,transformStyle:"flat", overflow:"hidden", backfaceVisibility:"hidden",force3D:true}),0);


			opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/1000,
							{	rotationX: nTR ==19 || nTR === 22 ? SDIR==1 ? -90 : 90 : 0,
								rotationY: nTR ==23 || nTR === 24 ? SDIR==1 ? -90 : 90 : 0,
								autoAlpha:nTR===22  || nTR===24 ? 1 : 0,
								left:0, top:0,scale:1, x:0,y:0
							}, {	overflow:"hidden",left:0,autoAlpha:1,rotationX:0, rotationY:0,top:0, scale:1, ease:ei}),0);

			opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/2000,{z:nTR==19 || nTR===23 ? -200 : 0},{z:nTR===19 || nTR===23? 0 : -200,ease:"power3.inOut",delay:(nTR===19 || nTR===23 ? MS/2000 : 0)}),0);

			if (nTR===22 || nTR===24) opt.mtl.add(tpGS.gsap.fromTo([PREV,CUR],MS/2000,{z:-200},{z:0,ease:"power2.in",delay: MS/2000 }),0);

			opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/2000,{z:0},{z:-200,ease:"power3.inOut",delay:0, force3D:'auto' }),0);
			if (nTR===19 || nTR===23) opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/2000,{autoAlpha:1},{autoAlpha:0,ease:"none",delay: MS/2000, force3D:'auto' }),0);


			opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/1000,
				{	overflow:"hidden", rotationX:0, rotationY:0,rotationZ:0,top:0, left:0, scale:1},
				{	rotationX:nTR===19 || nTR === 22 ? SDIR==1 ? 90 : -90 : 0,
					rotationY:nTR===23 || nTR === 24 ? SDIR==1 ? 90 : -90 : 0,

					overflow:"hidden",top:0, scale:1, delay:0,force3D:'auto',ease:ei}),0);
		} else

		////////////////////////////////////////
		// THE SLOTSLIDE - TRANSITION XX.  //
		///////////////////////////////////////
		if (nTR==20 ) {								// FLYIN
			var torig = SDIR===1 ? "20% " : "80% ";
			torig+="60% -50%";
			opt.mtl.add(tpGS.gsap.set(CUR,{transformOrigin:torig}),0);
			opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/1000,
			{left:SDIR===1 ? -opt.width : opt.width,rotationX:20,z:-opt.width, autoAlpha:0,top:0,scale:1,force3D:"auto",transformPerspective:600,transformOrigin:torig,rotationY:SDIR===1 ? 50 : -50},
			{left:0,rotationX:0,autoAlpha:1,top:0,z:0, scale:1,rotationY:0, delay:0,ease:ei}),0);
			torig = SDIR!=1 ? "20% " : "80% ";
			torig+="60% -50%";
			opt.mtl.add(tpGS.gsap.set(PREV,{transformOrigin:torig}),0);
			opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/1000,
			{autoAlpha:1,rotationX:0,top:0,z:0,scale:1,left:0, force3D:"auto",transformPerspective:600,transformOrigin:torig, rotationY:0},
			{autoAlpha:1,rotationX:20,top:0, z:-opt.width, left:SDIR!=1 ? -opt.width /1.2 : opt.width/1.2, force3D:"auto",rotationY:SDIR===1 ? -50 : 50, delay:0,ease:"power2.inOut"}),0);
		} else

		////////////////////////////////////////
		// THE SLOTSLIDE - TRANSITION XX.  //
		///////////////////////////////////////
		if (nTR==16 ) {								// PAPERCUT
			var torig = SDIR===1 ? "80% 50% 0" : "20%  50% 0";
			opt.mtl.add(tpGS.gsap.set(PREV,{zIndex:20}),0.001);
			opt.mtl.add(tpGS.gsap.set(CUR,{zIndex:10}),0.001);

			PREV.find('.slotslide').each(function(j) {
				opt.mtl.add(tpGS.gsap.fromTo(this,MS/1000,
				{rotationZ:0,opacity:1,top:0, left:0, z:0, scale:1},
				{opacity:1,left:SDIR===1 ? j==0 ? -opt.width/1.6 : -opt.width/1.8 : j===0 ? opt.width/1.6 : opt.width/1.8,rotationZ:SDIR===1 ? j===0 ? -35 : 25 : j===0 ? 25 : -35, z:0, top:j==0 ? "-120%" : "140%" ,scale:0.8 ,force3D:"auto",transformPerspective:600,transformOrigin:torig,delay:0,ease:ei}
				),0);
				opt.mtl.add(tpGS.gsap.fromTo(this,(MS/2000),{opacity:1},{opacity:0,delay:MS/2000}),0);
			});

			opt.mtl.add(tpGS.gsap.fromTo(CUR,(MS/1000) - (MS/7000),{x:Math.random()*100-50,opacity:1,scale:0.9, rotationZ:Math.random()*10-5},{x:0,opacity:1,scale:1,rotationZ:0, ease:ei, force3D:'auto',delay:(MS/7000)}),0);

		} else

		////////////////////////////////////////
		// THE SLOTSLIDE - TRANSITION XX.  //
		///////////////////////////////////////
		if (nTR==21 || nTR==25) {
			//ei = "power3.inOut";
			var rot = nTR===25 ? opt.rotate : SDIR===1 ? 90 : -90,
				rot2 = nTR===25 ? SDIR===1 ? -90 : 90 :  opt.rotate,
				torig = SDIR===1 ? nTR===25 ? "center top 0" : "left center 0" : nTR===25 ? "center bottom 0" : "right center 0";

			opt.mtl.add(tpGS.gsap.set(CUR,{transformOrigin:torig}),0);
			opt.mtl.add(tpGS.gsap.fromTo(CUR,MS/1000,{transformStyle:"flat",rotationX:rot2,top:0, left:0, autoAlpha:0,force3D:'auto',transformPerspective:1200,transformOrigin:torig,rotationY:rot},{autoAlpha:1,rotationX:0, rotationY:0,ease:ei}),0);

			torig = SDIR===1 ? nTR===25 ? "center bottom 0" : "right center 0" : nTR===25 ? "center top 0" : "left center 0";
			rot = nTR!==25 ? -rot : rot;
			rot2 = nTR!==25 ? rot2 : -rot2;
			opt.mtl.add(tpGS.gsap.set(PREV,{transformOrigin:torig}),0);
			opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/1000,{rotationX:0, rotationY:0,transformStyle:"flat",transformPerspective:1200,force3D:'auto'},{immediateRender:true, rotationX:rot2,transformOrigin:torig,rotationY:rot,ease:eo}),0);

		} else


		 ///////////////////////
		// NO TRANSITION //
		//////////////////////
		if (nTR==26) {
			MS=0;
			opt.mtl.add(tpGS.gsap.fromTo(CUR,0.001,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),0);
			opt.mtl.add(tpGS.gsap.to(PREV,0.001,{autoAlpha:0,force3D:"auto",ease:ei}),0);
			opt.mtl.add(tpGS.gsap.set(CUR.find('rs-sbg'),{autoAlpha:1}),0);
			opt.mtl.add(tpGS.gsap.set(PREV.find('rs-sbg'),{autoAlpha:1}),0);
		} else

		//////////////////////
		// SLIDING OVERLAYS //
		//////////////////////
		if (nTR==27||nTR==28||nTR==29||nTR==30) {

			var slot = CUR.find('.slot'),
				nd = nTR==27 || nTR==28 ? 1 : 2,
				mhp = nTR==27 || nTR==29 ? "-100%" : "100%",
				php = nTR==27 || nTR==29 ? "100%" : "-100%",
				mep = nTR==27 || nTR==29 ? "-80%" : "80%",
				pep = nTR==27 || nTR==29 ? "80%" : "-80%",
				ptp = nTR==27 || nTR==29 ? "10%" : "-10%",
				fa = {overwrite:"all"},
				ta = {autoAlpha:0,zIndex:1,force3D:"auto",ease:ei},
				fb = {position:"inherit",autoAlpha:0,overwrite:"all",zIndex:1},
				tb = {autoAlpha:1,force3D:"auto",ease:eo},
				fc = {overwrite:"all",zIndex:2,opacity:1,autoAlpha:1},
				tc = {autoAlpha:1,force3D:"auto",overwrite:"all",ease:ei},
				fd = {overwrite:"all",zIndex:2,autoAlpha:1},
				td = {autoAlpha:1,force3D:"auto",ease:ei},
				at = nd==1 ? "y" : "x";

			fa[at] = "0px";
			ta[at] = mhp;
			fb[at] = ptp;
			tb[at] = "0%";
			fc[at] = php;
			tc[at] = mhp;
			fd[at] = mep;
			td[at] = pep;

			slot.append('<span style="background-color:rgba(0,0,0,0.6);width:100%;height:100%;position:absolute;top:0px;left:0px;display:block;z-index:2"></span>');

			opt.mtl.add(tpGS.gsap.fromTo(PREV,MS/1000,fa,ta),0);
			opt.mtl.add(tpGS.gsap.fromTo(CUR.find('rs-sbg'),MS/2000,fb,tb),MS/2000);
			opt.mtl.add(tpGS.gsap.fromTo(slot,MS/1000,fc,tc),0);
			opt.mtl.add(tpGS.gsap.fromTo(slot.find('.slotslide div'),MS/1000,fd,td),0);
		}

		return opt.mtl;
	};

	///////////////////////
	// PREPARE THE SLIDE //
	//////////////////////
	var prepareOneSlide = function(wrap,opt,visible,vorh,order) {

		var slideBGFrom;

		for (var i in RVS.JHOOKS.prepareOneSlide) {
			if(!RVS.JHOOKS.prepareOneSlide.hasOwnProperty(i)) continue;
			slideBGFrom = RVS.JHOOKS.prepareOneSlide[i](slideBGFrom);
		}

		var slideSettings = RVS.F.getSlideBGDrawObj({slideBGFrom:slideBGFrom}),
			src = order!==0 ? slideSettings.backgroundImage : (RVS.C.slide.find('.slotwrapper_prev .defaultimg').css("backgroundImage")).replace('"','').replace('"',''),
			w = opt.width,
			h =opt.autoHeight=="on" ? opt.c.height() :  opt.height,
			img = wrap.find('rs-sbg'),
			mediafilter = "",
			scalestart = wrap.data('zoomstart'),
			rotatestart = wrap.data('rotationstart'),
			a=Math.ceil(w/opt.slots),
			b=Math.ceil(h/opt.slots),
			fulloff=0,
			fullyoff=0,
			bgfit = order!==0 ? slideSettings["background-size"] : "16px 16px",
			bgrepeat = order!==0 ? slideSettings.backgroundRepeat : "repeat",
			bgposition = slideSettings.backgroundPosition,
			bgstyle = slideSettings.backgroundColor ? 'background-color:' + slideSettings.backgroundColor + '; background-image:'+src+';'+'background-repeat:'+bgrepeat+';'+'background-size:'+bgfit+';background-position:'+bgposition
													: 'background:' + slideSettings.background + ';';


		if (img.data('currotate')!=undefined) rotatestart = img.data('currotate');
		if (img.data('curscale')!=undefined && vorh=="box") scalestart = img.data('curscale')*100;
		else
		if (img.data('curscale')!=undefined) scalestart = img.data('curscale');

		opt.slotw = b>a ? b : a;
		opt.sloth = b>a ? b : a;

		wrap.find('.slot').each(function() { jQuery(this).remove();});
		var off = 0;

		if (vorh==="box") { // BOX ANIMATION PREPARING
			var x = 0,
				y = 0;
			for (var j=0;j<opt.slots;j++) {
				y=0;
				for (var i=0;i<opt.slots;i++) {
					wrap.append('<div class="slot" style="z-index:4;position:absolute;overflow:hidden;top:'+(fullyoff+y)+'px;left:'+(fulloff+x)+'px;width:'+opt.slotw+'px;height:'+opt.sloth+'px;">'+
							  	'<div class="slotslide '+mediafilter+'" data-x="'+x+'" data-y="'+y+'" style="position:absolute;top:'+(0)+'px;left:'+(0)+'px;width:'+opt.slotw+'px;height:'+opt.sloth+'px;overflow:hidden;">'+
							  	'<div style="position:absolute;top:'+(0-y)+'px;left:'+(0-x)+'px;width:'+w+'px;height:'+h+'px;'+bgstyle+';">'+
							  	'</div></div></div>');
					y=y+opt.sloth;
					if (scalestart!=undefined && rotatestart!=undefined) tpGS.gsap.set(wrap.find('.slot').last(),{rotationZ:rotatestart});
				}
				x=x+opt.slotw;
			}
		} else
		if (vorh==="horizontal") {// SLOT ANIMATION PREPARING

			if (!visible) off=0-opt.slotw;
			for (var i=0;i<opt.slots;i++) {
				wrap.append('<div class="slot" style="z-index:4;position:absolute;overflow:hidden;top:'+(0+fullyoff)+'px;left:'+(fulloff+(i*opt.slotw))+'px;width:'+(opt.slotw+0.3)+'px;height:'+h+'px">'+
							'<div class="slotslide '+mediafilter+'" style="position:absolute;top:0px;left:'+off+'px;width:'+(opt.slotw+0.6)+'px;height:'+h+'px;overflow:hidden;">'+
							'<div style="position:absolute;top:0px;left:'+(0-(i*opt.slotw))+'px;width:'+w+'px;height:'+h+'px;'+bgstyle+';">'+
							'</div></div></div>');
				if (scalestart!=undefined && rotatestart!=undefined) tpGS.gsap.set(wrap.find('.slot').last(),{rotationZ:rotatestart});

			}
		}
		else if (vorh==="vertical") {
			if (!visible) off=0-opt.sloth;
			for (var i=0;i<opt.slots;i++) {
				wrap.append('<div class="slot" style="z-index:4;position:absolute;overflow:hidden;top:'+(fullyoff+(i*opt.sloth))+'px;left:'+(fulloff)+'px;width:'+w+'px;height:'+(opt.sloth)+'px">'+
							 '<div class="slotslide '+mediafilter+'" style="position:absolute;top:'+(off)+'px;left:0px;width:'+w+'px;height:'+opt.sloth+'px;overflow:hidden;">'+
							'<div style="position:absolute;top:'+(0-(i*opt.sloth))+'px;left:0px;width:'+w+'px;height:'+h+'px;'+bgstyle+';">'+
							'</div></div></div>');
				if (scalestart!=undefined && rotatestart!=undefined) tpGS.gsap.set(wrap.find('.slot').last(),{rotationZ:rotatestart});

			}
		}
		return opt;
	};
})();