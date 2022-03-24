/*!
 * REVOLUTION 6.0.0 UTILS OBJECT LIBRARY JS
 * @version: 1.0 (01.07.2019)
 * @author ThemePunch
*/

/**********************************
	-	GLOBAL VARIABLES	-
**********************************/
;window.RVS = window.RVS === undefined ? {} : window.RVS;
RVS.F = RVS.F === undefined ? {} : RVS.F;
RVS.ENV = RVS.ENV === undefined ? {} : RVS.ENV;
RVS.LIB = RVS.LIB === undefined ? {} : RVS.LIB;
RVS.V = RVS.V === undefined ? {} : RVS.V;
RVS.S = RVS.S === undefined ? {} : RVS.S;
RVS.C = RVS.C === undefined ? {} : RVS.C;
RVS.WIN = RVS.WIN === undefined ? jQuery(window) : RVS.WIN;
RVS.DOC = RVS.DOC === undefined ? jQuery(document) : RVS.DOC;

/**********************************
	-	OVERVIEW FUNCTIONS	-
********************************/
(function() {




	var filtericons = {images:"photo_camera",  modules:"aspect_ratio",  moduletemplates:"aspect_ratio", layers:"layers", videos:"videocam", svgs:"copyright", fonticons:"font_download", objects:"filter_drama"},
		objectSizes = {xs:10, s:25, m:50, l:75, o:100};

	/*
	INIT OVERVIEW
	*/
	RVS.F.initObjectLibrary = function(hideUpdateBtn) {
		initLocalListeners();
		RVS.F.buildObjectLibrary(hideUpdateBtn);
		RVS.LIB.OBJ.items = {};
		RVS.LIB.OBJ.search = 	jQuery('#searchobjects');
		RVS.LIB.OBJ.inited = true;
	};

	/*
	OPEN OVERVIEW
	*/
	RVS.F.openObjectLibrary = function(_) {

		//RVS.ENV.activated = false;

		RVS.LIB.OBJ.open = true;
		//moduletemplates, modules, layers, videos, svg, icons, images, favorite
		_ = _===undefined ? {types:"all",filter:"all", selected:["moduletemplates"], success:{slider:"addNewSlider"}} : _;


		RVS.S.isRTL = RVS.S.isRTL===undefined ? jQuery('body').hasClass("rtl") : RVS.S.isRTL;

		if (!RVS.LIB.OBJ.inited) RVS.F.initObjectLibrary();

		if (_.silent!==true) {
			// ANIMATE THINGS IN
			tpGS.gsap.fromTo(RVS.LIB.OBJ.container_Library,0.7,{scale:0.8,autoAlpha:0,display:"none"},{autoAlpha:1,display:"block",scale:1,ease:"power3.inOut"});
			tpGS.gsap.fromTo('#ol_header, #ol_footer',0.5,{autoAlpha:0,ease:"power3.inOut"},{autoAlpha:1,opacity:1,ease:"power3.inOut",delay:0.5});
		}

		RVS.LIB.OBJ.staticalso = _.staticalso;
		RVS.LIB.OBJ.success = _.success;
		RVS.LIB.OBJ.selectedType = _.selected[0];
		RVS.LIB.OBJ.selectedFilter = _.filter;
		RVS.LIB.OBJ.selectedFolder = -1;
		RVS.LIB.OBJ.selectedPage = 0;
		RVS.LIB.OBJ.selectedPackage = -1;	//IN WHICH PACKAGE WE ARE IN
		RVS.LIB.OBJ.selectedModule = -1;	//IN WHICH
		RVS.LIB.OBJ.selectedModuleTitle = "";
		RVS.LIB.OBJ.slideParent = -1;
		RVS.LIB.OBJ.reDownloadTemplate = false;
		RVS.LIB.OBJ.createBlankPage = false;
		RVS.LIB.OBJ.data = _.data;
		RVS.LIB.OBJ.context = _.context===undefined ? "overview" : "editor";
		RVS.LIB.OBJ.depth = _.depth===undefined ? "slide" : _.depth;


		jQuery('.ol_filter_type.selected').removeClass("selected");
		jQuery('.ol_filter_type.open').removeClass("open");

		if (_.types!=="all") {
			RVS.LIB.OBJ.container_Filters.find('.ol_filter_type').each(function() {

				if (jQuery.inArray(this.dataset.type,_.types)>=0)
					jQuery(this).show();
				else
					jQuery(this).hide();
			});

		} else {
			RVS.LIB.OBJ.container_Filters.find('.ol_filter_type').show();
		}

		// SELECT PREDEFINED ELEMENT
		var mod = jQuery('#ol_filter_'+_.selected);
		mod.addClass('open');
		mod.find('.ol_filter_headerelement').addClass("selected");

		mod.find('.ol_filter_listelement[data-filter="'+_.filter+'"]').addClass("selected");
		updateSearchPlaceHolder(true);

		//LOAD ITEMS AND CALL FURTHER FUNCTIONS
		RVS.F.loadLibrary({modules:_.selected, event:(_.event!==undefined ? _.event : "reBuildObjectLibrary")});

		if (_.updatelist===false)
			jQuery('#obj_updatefromserver').hide();
		else
			jQuery('#obj_updatefromserver').show();

	};

	/*
	REBUILD THE OBJECT LIBRARY RIGHT SIDE
	*/
	RVS.F.reBuildObjectLibrary = function() {
		RVS.F.updateFilteredList();
	};



	/*
	UPDATE THE LIBRARY FROM SERVER
	*/
	RVS.F.updateObjectLibraryFromServer = function(obj) {
		RVS.F.removeModuleTemplatesFromLibrary(obj);
		RVS.LIB.OBJ.refreshFromServer = true;
		RVS.F.loadLibrary({modules:[obj], event:"reBuildObjectLibrary"});
	};

	/*
	REMOVE UNNEEDED THINGS FOR THE LIBRARY TO UPDATE IT
	*/
	RVS.F.removeModuleTemplatesFromLibrary = function(obj) {
		delete RVS.LIB.OBJ.types[obj];
		delete RVS.LIB.OBJ.items[obj];
		RVS.LIB.OBJ.selectedType=obj;
		RVS.LIB.OBJ.lastSelectedType=obj;
		RVS.LIB.OBJ.filteredList = [];
		RVS.LIB.OBJ.oldList = [];
		RVS.LIB.OBJ.pages = [];
		RVS.LIB.OBJ.container_Output[0].innerHTML = "";

	};

	function rebuildObjectFilter(type) {
		jQuery('#ol_filter_'+type).remove();
		addObjectFilter({groupType:type, groupAlias:RVS_LANG['ol_'+type], icon:filtericons[type], count:RVS.LIB.OBJ.types[type].count, tags:RVS.LIB.OBJ.types[type].tags, groupopen:true});
	}

	/*
	LOAD THE ELEMENTS TO A LIBRARY
	*/
	RVS.F.loadLibrary = function(_) {

		// CHECK ALREADY LOADED LIBRARIES
		var toload = [],
			loaded = [];

		for (var i in _.modules) {
			if(!_.modules.hasOwnProperty(i)) continue;
			RVS.LIB.OBJ.types[_.modules[i]] = RVS.LIB.OBJ.types[_.modules[i]]===undefined ? {} : RVS.LIB.OBJ.types[_.modules[i]];
			if (RVS.LIB.OBJ.types[_.modules[i]].loaded !== true)
				toload.push(_.modules[i]);
			else
				loaded.push(_.modules[i]);
		}

		// TRY TO LOAD ELEMENTS
		if (toload.length>0)
			RVS.F.ajaxRequest('load_module', {module:toload, refresh_from_server:RVS.LIB.OBJ.refreshFromServer}, function(response){
				if(response.success) {
					for (var type in response.modules) {
						if(!response.modules.hasOwnProperty(type)) continue;
						RVS.LIB.OBJ.items[type] = RVS.LIB.OBJ.items[type]===undefined ? [] : RVS.LIB.OBJ.items[type];
						for (var id in response.modules[type].items) {
							if(!response.modules[type].items.hasOwnProperty(id)) continue;
							RVS.LIB.OBJ.items[type][id] = response.modules[type].items[id];
							RVS.LIB.OBJ.items[type][id].libraryType = type;
							if (RVS.LIB.OBJ.items[type][id].id===undefined)
								RVS.LIB.OBJ.items[type][id].id = id;
						}
						if (response.modules[type].tags!==undefined) {
							RVS.LIB.OBJ.types[type].tags = response.modules[type].tags;
							rebuildObjectFilter(type);
						}
						RVS.LIB.OBJ.types[type].loaded = true;
					}
					if (_.event!==undefined) RVS.DOC.trigger(_.event, _.eventparam);

					// trigger custom callback onload (for shortcode wizard)
					if(RVS.LIB.OBJ.success && RVS.LIB.OBJ.success.event) {

						let param = RVS.LIB.OBJ.success.eventparam || false;
						RVS.DOC.trigger(RVS.LIB.OBJ.success.event, param);

					}
				}
			});

		// EVENT NEED TO BE TRIGGERED, NOTHING TO LOAD
		if (loaded.length>0 && toload.length===0 && _.event!==undefined) {
			RVS.DOC.trigger(_.event, _.eventparam);
		}
		RVS.LIB.OBJ.refreshFromServer = false;
	};

	/*
	LOAD SLIDES TO MODULES OR MODULE TEMPLATES
	*/
	RVS.F.loadSimpleModule = function(_) {

		var exists = false;
		for (var i in RVS.LIB.OBJ.items[_.modules[0]]) {
			if(!RVS.LIB.OBJ.items[_.modules[0]].hasOwnProperty(i)) continue;
			exists = exists===true ? true : RVS.LIB.OBJ.items[_.modules[0]][i].parent==_.moduleid;
		}

		if (!exists)
			RVS.F.ajaxRequest('load_module', {module:_.modules[0], module_id:_.moduleid, module_uid:_.module_uid, static:RVS.LIB.OBJ.staticalso}, function(response){
				if(response.success) {
					for (var type in response.modules) {
						if(!response.modules.hasOwnProperty(type)) continue;
						RVS.LIB.OBJ.items[type] = RVS.LIB.OBJ.items[type]===undefined ? [] : RVS.LIB.OBJ.items[type];
						var lastid = RVS.LIB.OBJ.items[type].length,
							sindex = RVS.F.getModuleIndex(_.moduleid,_.parenttype),
							parenttitle = RVS.LIB.OBJ.items[_.parenttype][sindex].title;
						for (var id in response.modules[type].items) {
							if(!response.modules[type].items.hasOwnProperty(id)) continue;
							response.modules[type].items[id].libraryType = type;
							response.modules[type].items[id].moduleid = _.moduleid;
							response.modules[type].items[id].module_uid = _.module_uid;
							response.modules[type].items[id].parenttitle = parenttitle;
							response.modules[type].items[id].slideid = response.modules[type].items[id].id===undefined ? id : response.modules[type].items[id].id;
							response.modules[type].items[id].id = parseInt(lastid,0)+parseInt(id,0);
							RVS.LIB.OBJ.items[type].push(response.modules[type].items[id]);
						}
					}
					if (_.event!==undefined) RVS.DOC.trigger(_.event, _.eventparam);
				}
			});
		else
			if (_.event!==undefined) RVS.DOC.trigger(_.event, _.eventparam);
	};

	RVS.F.addonInstalledOnDemand = function(addon) {
		var changed = false;
		if (RVS.LIB.OBJ===undefined || RVS.LIB.OBJ.items===undefined) return;
		for (var i in RVS.LIB.OBJ.items.moduletemplates) {
			if(!RVS.LIB.OBJ.items.moduletemplates.hasOwnProperty(i)) continue;
			var item = RVS.LIB.OBJ.items.moduletemplates[i];
			for (var j in item.plugin_require) {
				if(!item.plugin_require.hasOwnProperty(j)) continue;
				var lg = item.plugin_require[j].path.split("/");
				lg = lg[lg.length-1].split('.php')[0];
				if (lg===addon || item.plugin_require[j].name===addon) {
					item.plugin_require[j].installed = true;
					if (item && item.ref) item.ref.remove();
					delete item.ref;
					changed=true;
				}
			}
		}
		if (changed) RVS.F.updateFilteredList({force:true,keeppage:false,noanimation:false});
	};

	/*
	UPDATE THE PARENT ATTRIBUTES ON THE SINGLE SLIDERS AND FOLDERS
	*/
	RVS.F.updateParentAttributes = function() {
		if (window.parentAttributesUpdateForObjects) return false;
		window.parentAttributesUpdateForObjects = true;
		for (var i in RVS.LIB.OBJ.items.modules) {
			if(!RVS.LIB.OBJ.items.modules.hasOwnProperty(i)) continue;
			if (RVS.LIB.OBJ.items.modules[i].folder) {
				for (var c in RVS.LIB.OBJ.items.modules[i].children) {
					if(!RVS.LIB.OBJ.items.modules[i].children.hasOwnProperty(c)) continue;
					var sindex = RVS.F.getSliderIndex(RVS.LIB.OBJ.items.modules[i].children[c]);
					if (sindex!==-1) RVS.LIB.OBJ.items.modules[sindex].parent = RVS.LIB.OBJ.items.modules[i].id;
				}
			}
		}
	};


	// UPDATE THE CURRENT VISIBILITY LIST
	RVS.F.updateFilteredList = function(_) {

		_ = _===undefined ? {force:false,keeppage:false,noanimation:false, focusItem:false} : _;
		if (RVS.LIB.OBJ.selectedPackage!==-1) {
			RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].sort(function(b,a) { return b.package_order - a.package_order;});
			RVS.LIB.OBJ.container_Sorting.hide();
		} else {
			RVS.LIB.OBJ.container_Sorting.show();
			//Sort the Sliders First
			switch(RVS.LIB.OBJ.container_Library.find('#sel_olibrary_sorting').select2RS('data')[0].id) {
				case "datedesc":
					if ((RVS.LIB.OBJ.selectedType==="moduletemplateslides" || RVS.LIB.OBJ.selectedType==="moduleslides"))
						RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].sort(function(b,a) { return b.id - a.id;});
					else
						RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].sort(function(a,b) { return b.id - a.id;});
				break;
				case "title":
					RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].sort(function(a,b) { return a.title.toUpperCase().localeCompare(b.title.toUpperCase()); });
				break;
				case "titledesc":
					RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].sort(function(a,b) { return b.title.toUpperCase().localeCompare(a.title.toUpperCase()); });
				break;
				default:
						RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].sort(function(a,b) { return a.id - b.id;});
				break;
			}
		}
		RVS.LIB.OBJ.selectedFolder = parseInt(RVS.LIB.OBJ.selectedFolder,0);
		RVS.LIB.OBJ.oldlist = RVS.LIB.OBJ.filteredList;
		RVS.LIB.OBJ.filteredList = [];
		var s = jQuery('#searchobjects').val().toLowerCase(),
			checkfavorit = jQuery('#obj_fil_favorite').hasClass("selected");
		if (RVS.LIB.OBJ.selectedType==="modules") RVS.F.updateParentAttributes();

		// ADD SLIDES IF MODULETPYE EXISTS , ORSLIDERS


		for (var i in RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType]) {
			if(!RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].hasOwnProperty(i)) continue;
			var obj = RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][i];
			/* addToFilter = false; */
			obj.parent = obj.parent===undefined ? -1 : obj.parent;
			var folderPath = getParentPath(obj.parent);


			// SEARCHED && obj IS CHILDREN FROM SELECTED FOLDER && SEARCHED TEXT IN TITLE OR TAGLIST
			if (!checkfavorit || obj.favorite) {
				var samefolder = (jQuery.inArray(RVS.LIB.OBJ.selectedFolder,folderPath)>=0 ||  jQuery.inArray(""+RVS.LIB.OBJ.selectedFolder,folderPath)>=0);
				if ((s.length>2 && samefolder && (obj.title.toLowerCase().indexOf(s)>=0) && (RVS.LIB.OBJ.selectedFilter=="all" || filterMatch({o:obj, filter:RVS.LIB.OBJ.selectedFilter}))) ||
					(s.length<3 && RVS.LIB.OBJ.selectedType=== obj.libraryType && RVS.LIB.OBJ.selectedFilter=="all" && parseInt(obj.parent,0) == RVS.LIB.OBJ.selectedFolder) ||
					(s.length<3 && RVS.LIB.OBJ.selectedType=== obj.libraryType && filterMatch({o:obj, filter:RVS.LIB.OBJ.selectedFilter}) && samefolder) ||
					(RVS.LIB.OBJ.selectedType==="moduletemplateslides" || RVS.LIB.OBJ.selectedType==="moduleslides"))
						if (((checkfavorit && obj.favorite) && (RVS.LIB.OBJ.selectedPackage==-1 ||  obj.package_id == RVS.LIB.OBJ.selectedPackage)) ||
							((RVS.LIB.OBJ.selectedType==="moduletemplates") && (s.length>2 || (((RVS.LIB.OBJ.selectedPackage==-1 && (obj.package_id==undefined ||  obj.package_parent=="true")) || (RVS.LIB.OBJ.selectedPackage!==-1 && (obj.package_id == RVS.LIB.OBJ.selectedPackage) && obj.package_parent!="true") ) ))) ||
							((RVS.LIB.OBJ.selectedType==="moduletemplateslides" || RVS.LIB.OBJ.selectedType==="moduleslides") && RVS.LIB.OBJ.selectedModule==obj.parent) ||
							(RVS.LIB.OBJ.selectedType!=="moduletemplates" && RVS.LIB.OBJ.selectedType!=="moduletemplateslides" && RVS.LIB.OBJ.selectedType!=="moduleslides")
							)
								RVS.LIB.OBJ.filteredList.push(obj.id);


			}
		}
		// ONLY REDRAW WHEN FORCED OR FILTERED RESULT CHANGED
		if(_.force || JSON.stringify(RVS.LIB.OBJ.oldlist) !== JSON.stringify(RVS.LIB.OBJ.filteredList)){
			RVS.F.buildPagination({keeppage:_.keeppage, focusItem:_.focusitem});
			RVS.F.drawOverview({noanimation:_.noanimation, focusItem:_.focusItem});
		}
		RVS.LIB.OBJ.container_OutputWrap.RSScroll("update");
	};


	/*
	DRAW AN OVERVIEW LIST WITH PRESELECTED FILTERS AND SIZES
	*/
	RVS.F.drawOverview = function(_) {

		_ = _ === undefined ? {noanimation:false} : _;
		RVS.LIB.OBJ.container_Output.find('.rsl_breadcrumb_wrap').remove();


		if (RVS.LIB.OBJ.selectedFolder!==-1 || RVS.LIB.OBJ.selectedPackage!==-1 || RVS.LIB.OBJ.selectedModule!==-1) {
			var bread = '<div class="rsl_breadcrumb_wrap">';
			bread += '<div class="rsl_breadcrumb" data-folderid="-1"><i class="material-icons">apps</i>'+RVS_LANG.simproot+'</div>';
			bread += '<i class="rsl_breadcrumb_div material-icons">keyboard_arrow_right</i>';

			var folderlist = '';
			if (RVS.LIB.OBJ.selectedFolder!==-1) {
				var pd = RVS.LIB.OBJ.selectedFolder,
					quit = 0;

				while (pd !== -1 && quit!==100) {
					var foldertype = RVS.LIB.OBJ.selectedType==="moduleslides" ? "modules" : RVS.LIB.OBJ.selectedType,
						sindex = RVS.F.getModuleIndex(pd,foldertype);
					if (sindex!==-1 && sindex!=="-1") {
						folderlist = '<div class="rsl_breadcrumb" data-folderid="'+pd+'"><i class="material-icons">folder_open</i>'+RVS.LIB.OBJ.items[foldertype][sindex].title+'</div>' + '<i class="rsl_breadcrumb_div material-icons">keyboard_arrow_right</i>' + folderlist;
						pd = RVS.LIB.OBJ.items[foldertype][sindex].parent || -1;
						quit++;
					} else {
						quit=100;
						RVS.LIB.OBJ.selectedModule = -1;
						RVS.LIB.OBJ.selectedModuleTitle = "";
						RVS.LIB.OBJ.selectedModuleType = "";
						unselectOLItems();
						if (RVS.LIB.OBJ.selectedType==="moduletemplates")
							RVS.LIB.OBJ.selectedPackage = -1;
						if (RVS.LIB.OBJ.selectedType==="modules") {
							RVS.LIB.OBJ.selectedFolder = -1;
							RVS.F.resetAllFilters();
						}
						RVS.F.updateFilteredList({force:true,keeppage:true,noanimation:true});
					}
				}
				bread += folderlist;
			}
			bread += RVS.LIB.OBJ.selectedPackage!==-1 ? '<div id="rsl_bread_selected" data-folderid="'+RVS.LIB.OBJ.selectedPackage+'" class="rsl_breadcrumb">'+RVS.LIB.OBJ.selectedPackageTitle+'</div>' : '<div id="rsl_bread_selected" class="rsl_breadcrumb"></div>';
			bread += RVS.LIB.OBJ.selectedModule!==-1 ? RVS.LIB.OBJ.selectedPackage!==-1 ? '<i class="rsl_breadcrumb_div material-icons">keyboard_arrow_right</i>' + '<div id="rsl_bread_selected" class="rsl_breadcrumb">'+RVS.LIB.OBJ.selectedModuleTitle+'</div>' : '<div id="rsl_bread_selected" class="rsl_breadcrumb">'+RVS.LIB.OBJ.selectedModuleTitle+'</div>' : '<div id="rsl_bread_selected" class="rsl_breadcrumb"></div>';
			bread += '</div>';
			RVS.LIB.OBJ.container_Output.append(bread);
		}



		//HIDE ALL OLD SELECTED TYPE
		if (RVS.LIB.OBJ.lastSelectedType!==undefined && RVS.LIB.OBJ.lastSelectedType !== RVS.LIB.OBJ.selectedType) for (var i in RVS.LIB.OBJ.items[RVS.LIB.OBJ.lastSelectedType]) if (RVS.LIB.OBJ.items[RVS.LIB.OBJ.lastSelectedType].hasOwnProperty(i)) if (RVS.LIB.OBJ.items[RVS.LIB.OBJ.lastSelectedType][i].ref!==undefined) RVS.LIB.OBJ.items[RVS.LIB.OBJ.lastSelectedType][i].ref.detach();

		RVS.LIB.OBJ.lastSelectedType = RVS.LIB.OBJ.selectedType;
		RVS.LIB.OBJ.selectedPage = RVS.LIB.OBJ.selectedPage===undefined ? 1 : RVS.LIB.OBJ.selectedPage;

		// PREPARE AJAX LOADS
		RVS.LIB.OBJ.waitForLoad = [];
		RVS.LIB.OBJ.waitForLoadIndex = 0;

		for (var i in RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType]) {
			if(!RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].hasOwnProperty(i)) continue;
			var obj = RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][i];
			if (jQuery.inArray(obj.id,RVS.LIB.OBJ.pages[RVS.LIB.OBJ.selectedPage-1])>=0) {
				if (obj.ref===undefined) {
					if (obj.img!==undefined && ((typeof obj.img==="object" && obj.img.url.indexOf("//")===-1) || (typeof obj.img!=="object" && obj.img.indexOf("//")===-1)))
							RVS.LIB.OBJ.waitForLoad.push({librarytype:obj.libraryType, mediatype:"img", ind:i, id:(typeof obj.img==="object" ? obj.img.url : obj.img)});

					if (obj.video_thumb!==undefined && ((typeof obj.video_thumb==="object" && obj.video_thumb.url.indexOf("//")===-1) || (typeof obj.video_thumb!=="object" && obj.video_thumb.indexOf("//")===-1)))
							RVS.LIB.OBJ.waitForLoad.push({librarytype:obj.libraryType, mediatype:"video", ind:i, id:(typeof obj.video_thumb==="object" ? obj.video_thumb.url : obj.img)});

				}
			}
		}

		RVS.F.loadAllMissingMedia();

	};

	// Loading Missing Medias In 1 Go
	RVS.F.loadAllMissingMedia = function() {

		if (RVS.LIB.OBJ.waitForLoad.length>0) {
			if (RVS.LIB.OBJ.waitForLoadIndex<RVS.LIB.OBJ.waitForLoad.length) {
				var half = (RVS.LIB.OBJ.waitForLoad[0].librarytype==="layers" || RVS.LIB.OBJ.waitForLoad[0].librarytype==="videos") ? Math.round(RVS.LIB.OBJ.waitForLoad.length/2) : RVS.LIB.OBJ.waitForLoad.length;
				half = RVS.LIB.OBJ.waitForLoad[0].librarytype==="videos" ? Math.round(RVS.LIB.OBJ.waitForLoad.length/2)+" "+RVS_LANG.elements+" ("+Math.round((RVS.LIB.OBJ.waitForLoad.length/2) * 450)/100+"MB)" :
						RVS.LIB.OBJ.waitForLoad[0].librarytype==="layers" ? Math.round(RVS.LIB.OBJ.waitForLoad.length/2)+" "+RVS_LANG.elements+" ("+Math.round((RVS.LIB.OBJ.waitForLoad.length/2) * 25)/100+"MB)" :
						Math.round(RVS.LIB.OBJ.waitForLoad.length)+" "+RVS_LANG.elements+" ("+Math.round((RVS.LIB.OBJ.waitForLoad.length) * 1.5)/100+"MB)";

				RVS.F.ajaxRequest('load_library_image', RVS.LIB.OBJ.waitForLoad, function(response){
					if (response.success) {
						for (var i in response.data) {
							if(!response.data.hasOwnProperty(i)) continue;
							var ld = response.data[i],
							obj = RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][ld.ind];
							if (ld.mediatype==="img") if (typeof obj.img==="object") obj.img.url = ld.url; else obj.img = ld.url;
							if (ld.mediatype==="video") if (typeof obj.video_thumb==="object") obj.video_thumb.url = ld.url; else obj.video_thumb = ld.url;
						}
						RVS.F.finalDrawOfElements();
					} else {
						console.log("Could Not be loaded. Please try later.");
						RVS.F.finalDrawOfElements();
					}
				},undefined,undefined,RVS_LANG.loadingthumbs+'<br><span style="font-size:17px; line-height:25px;">'+RVS_LANG.loading+" "+half+'</span>');
			}
		}
		else
			RVS.F.finalDrawOfElements();
	};

	RVS.F.finalDrawOfElements = function() {
		var d = 0;
		// SHOW /HIDE SIMILAR TYPES BASED ON PAGINATION
		for (var i in RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType]) {
			if(!RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].hasOwnProperty(i)) continue;
			var obj = RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][i];
			if (jQuery.inArray(obj.id,RVS.LIB.OBJ.pages[RVS.LIB.OBJ.selectedPage-1])>=0) {
				d++;
				if ( obj.ref!==undefined && obj.folder) obj.ref.remove();
				obj.ref = obj.ref===undefined || obj.folder ? RVS.F.buildElement(obj) : obj.ref;
				obj.ref.appendTo(RVS.LIB.OBJ.container_Output);
			} else 	if (obj.ref!==undefined) obj.ref.detach();
		}
		if (RVS.LIB.OBJ.selectedType==="moduletemplates") RVS.F.initOnOff(RVS.LIB.OBJ.container_Output);
		updateScollbarFilters();
	};

	/*
	BUILD ONE SINGLE ELEMENT IN THE OVERVIEW
	*/
	RVS.F.buildElement = function(_,withouttoolbar) {



		/* var folderclass = _.folder ? "folder_library_element" : "", */
		/* imgobjunder = jQuery('<div class="image_container_underlay"></div>'), */

		var objhtml = '<div data-objid="'+_.id+'" id="'+_.id+'" class="olibrary_item">';
		objhtml += '	<div class="olibrary_media_wrap"></div>';
		objhtml += '	<div class="olibrary_content_wrap">';
		objhtml += '	</div>';
		objhtml += '</div>';

		var obj = jQuery(objhtml),
			cwrap  = obj.find('.olibrary_content_wrap'),
			iwrap = obj.find('.olibrary_media_wrap'),
			content ="",
			infocontent="",
			o_ok ='<i class="olti_icon olti_green material-icons">check</i>',
			o_no ='<i class="olti_icon olti_red material-icons">close</i>';

		switch (_.libraryType) {
			case "moduletemplates":

				var installable = true,
					packinstallable = true;
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content +=_.package_parent=="true" ? '	<div class="olibrary_content_type oc_package">'+RVS_LANG.packageBIG+'</div>' : '	<div class="olibrary_content_type oc_purple">'+RVS_LANG.moduleBIG+'</div>';
				content += '	<div class="installed_notinstalled olibrary_content_info oc_gray">'+(_.installed ? RVS_LANG.installed : RVS_LANG.notinstalled)+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.id+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';
				infocontent += '<div class="ol_template_info_wrap">';
				infocontent += '<div class="olti_title">'+_.title+'</div>';
				infocontent += _.description;
				infocontent += '<div class="div30"></div>';
				infocontent += '<div class="olti_title">'+RVS_LANG.setupnotes+'</div>';
				infocontent += _.setup_notes;
				if (_.required!==undefined || (_.plugin_require!==undefined  && _.plugin_require!==null)) {
					infocontent += '<div class="div30"></div>';
					infocontent += '<div class="olti_title">'+RVS_LANG.requirements+'</div>';
					if (_.required!==undefined) infocontent += '<div class="olti_content">'+(_.required <= RVS.ENV.revision ? o_ok : o_no)+'Slider Revolution Version '+_.required+'</div>';
					if (_.required > RVS.ENV.revision) installable=false;
					if (_.plugin_require!==undefined  && _.plugin_require!==null) {
						for (var pi in _.plugin_require) {
							if(!_.plugin_require.hasOwnProperty(pi)) continue;
							infocontent += '<div class="olti_content">'+(_.plugin_require[pi].installed=="true" || _.plugin_require[pi].installed==true? o_ok : o_no)+'<a href="'+_.plugin_require[pi].url+'" target="_blank">'+_.plugin_require[pi].name+'</a></div>';
							if (_.plugin_require[pi].installed!=="true" && _.plugin_require[pi].installed!==true) installable=false;
						}
					}
				}

				installable = RVS.ENV.activated===false ? false : installable;


				// WHICH ICON TO SHOW ON MODULES, FOLDERS, PACKAGES BASED ON ACTIVATION AND REQUIRED PLUGINS
				if (RVS.LIB.OBJ.context==="editor") {
					if (_.package_parent=="true")
						obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-elementtype="package_parent" data-title="'+_.title+'" data-packageid="'+_.package_id+'" class="material-icons ol_link_to_deeper">folder</i></div>');
					else
					if (installable)
						obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-moduleid="'+_.id+'" data-module_uid="'+_.uid+'" data-elementtype="module_parent" data-title="'+_.title+'" data-packageid="'+_.id+'" class="material-icons ol_link_to_deeper">burst_mode</i></div>');
					else
						obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-elementtype="" class="material-icons ol_link_to_add">burst_mode</i></div>');
				} else {
					if (_.package_parent=="true")
						obj.append('<div class="olibrary_media_overlay threeicons"><i data-librarytype="'+_.libraryType+'" data-elementtype="" class="material-icons ol_link_to_add">add</i><i data-librarytype="'+_.libraryType+'" data-elementtype="package_parent" data-title="'+_.title+'" data-packageid="'+_.package_id+'" class="material-icons ol_link_to_deeper">folder</i><i data-preview="'+_.preview+'" data-librarytype="'+_.libraryType+'" data-elementtype="" class="material-icons ol_link_to_view">visibility</i></div>');
					else
						obj.append('<div class="olibrary_media_overlay"><i data-librarytype="'+_.libraryType+'" data-elementtype="" class="material-icons ol_link_to_add">add</i><i data-librarytype="'+_.libraryType+'" data-elementtype=""  data-preview="'+_.preview+'" class="material-icons ol_link_to_view">visibility</i></div>');
				}

				var pckg;
				// IF PACKAGE KID, CHECK PARRENT PACKAGE DEPENDENCIES
				if (_.package_id!==undefined && _.package_id!==-1) {
					pckg = isPackageInstallable({packageId:_.package_id});
					if (_.package_parent!="true" && pckg.installable===false) packinstallable = false;
				}

				infocontent += '<div class="div30"></div>';
				infocontent += '<div class="olti_title">'+RVS_LANG.availableversion+'</div>';
				infocontent += '<div class="olti_content">'+_.version+'</div>';
				infocontent += '<div class="div30"></div>';

				var activateadded = false;
				// IS THE ITEM INSTALLABLE ?
				if (_.package_parent!="true") {
					if (RVS.ENV.activated) {
						if (installable)
							infocontent += '<div data-title="'+_.title+'" data-uid="'+_.uid+'" class="olti_btn olti_install_template"><i class="material-icons">file_download</i>'+RVS_LANG.installtemplate+'</div>';
						else
							infocontent += '<div class="olti_btn olti_install_template notinstallable"><i class="material-icons">file_download</i>'+RVS_LANG.installtemplate+'</div>';
					} else {
						activateadded = true;
						infocontent += '<div class="olti_btn olti_install_template notinstallable"><i class="material-icons">file_download</i>'+RVS_LANG.licencerequired+'</div>';
					}
					if (_.package_id!==undefined && _.package_id!==-1) infocontent += '<div class="div10"></div>';
				}

				// IS THE PACKAGE INSTALLABLE ?
				if (_.package_id!==undefined && _.package_id!==-1)
					if (RVS.ENV.activated) {
						if (installable && packinstallable)
							infocontent += '<div data-package="'+_.package+'" data-folderuid="'+_.uid+'" data-uids="'+pckg.uids.toString()+'" class="olti_btn olti_install_template_package"><i class="material-icons">file_download</i>'+RVS_LANG.installpackage+'</div>';
						else
							infocontent += '<div class="olti_btn olti_install_template_package notinstallable"><i class="material-icons">file_download</i>'+RVS_LANG.installpackage+'</div>';
					} else {
						if (!activateadded) infocontent += '<div class="olti_btn olti_install_template_package notinstallable"><i class="material-icons">file_download</i>'+RVS_LANG.licencerequired+'</div>';
					}

				//REDOWNLOAD CHECK
				if ((_.package_parent!="true" && installable) || (_.package_id!==undefined && _.package_id!==-1 && installable && packinstallable)) {
					infocontent += '<div class="div20"></div>';
					infocontent += '<div class="olti_content"><input type="checkbox" class="redownloadTemplateState"/>'+RVS_LANG.redownloadTemplate+'</div>';
					infocontent += '<div class="olti_content"><input type="checkbox" class="createBlankPageState"/>'+RVS_LANG.createBlankPage+'</div>';
				}

				infocontent += '</div>';
				if (_.img!==undefined && jQuery.type(_.img)==="string") tpGS.gsap.set(iwrap,{backgroundImage:'url('+_.img+')', "background-size":"cover", backgroundPosition:"center center"});
				else
				if (_.img!==undefined && jQuery.type(_.img)==="object") {
					var imgobj = _.img.style!==undefined ? jQuery('<div class="olibrary_media_style" style="'+_.img.style+'"></div>') : jQuery('<div class="olibrary_media_style"></div>');
					if (_.img.url!==undefined && _.img.url.length>3)  tpGS.gsap.set(imgobj,{backgroundImage:"url("+_.img.url+")"});
					iwrap.append(imgobj);
				}
			break;
			case "moduleslides":
			case "moduletemplateslides":


				var installable = true,
					packinstallable = true;
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content +=_.package_parent=="true" ? '	<div class="olibrary_content_type oc_package">'+RVS_LANG.packageBIG+'</div>' : '	<div class="olibrary_content_type oc_purple">'+RVS_LANG.moduleBIG+'</div>';

				if (_.libraryType==="moduletemplateslides")
					if (_.required!==undefined || (_.plugin_require!==undefined  && _.plugin_require!==null)) {
						if (_.required > RVS.ENV.revision) installable=false;
						if (_.plugin_require!==undefined  && _.plugin_require!==null)
							for (var pi in _.plugin_require) {
								if(!_.plugin_require.hasOwnProperty(pi)) continue;
								if (_.plugin_require[pi].installed!="true" && installable) {
									installable=false;
								}
							}

					}
				// SHOW THE LAYERS IF SLIDE SELECTED
				if (RVS.LIB.OBJ.depth==="layers") {
					obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'"  data-parenttitle="'+_.parenttitle+'"  data-parent="'+_.parent+'" data-id="'+_.id+'" data-slideid="'+_.slideid+'" data-slidetitle="'+_.title+'" class="material-icons ol_link_to_deeper">layers</i></div>');
					setObjBg(_,iwrap);
				} else {
					// IF INSTALLABLE, ADD "INSTALL"
					if (installable) {
						obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'"  data-parenttitle="'+_.parenttitle+'"  data-parent="'+_.parent+'" data-id="'+_.id+'" data-parentuid="'+_.module_uid+'" class="material-icons ol_link_to_add">add</i></div>');
						content += _.libraryType==="moduletemplateslides" ? '	<div class="installed_notinstalled olibrary_content_info oc_gray">'+(_.installed ? RVS_LANG.installed : RVS_LANG.notinstalled)+'</div>' : '';
						content += '</div>';
					}
					if (_.libraryType==="moduletemplateslides") {
						if (_.img!==undefined && jQuery.type(_.img)==="string") tpGS.gsap.set(iwrap,{backgroundImage:'url('+_.img+')', "background-size":"cover", backgroundPosition:"center center"});
						else
						if (_.img!==undefined && jQuery.type(_.img)==="object") {
							var imgobj = _.img.style!==undefined ? jQuery('<div class="olibrary_media_style" style="'+_.img.style+'"></div>') : jQuery('<div class="olibrary_media_style"></div>');
							if (_.img.url!==undefined && _.img.url.length>3)  tpGS.gsap.set(imgobj,{backgroundImage:"url("+_.img.url+")"});
							iwrap.append(imgobj);
						}
					} else 	setObjBg(_,iwrap);

				}
			break;
			case "svgs":
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content += '	<div class="olibrary_content_type oc_green">'+RVS_LANG.iconBIG+'</div>';
				content += '	<div class="olibrary_content_info oc_gray">'+RVS_LANG.svgBIG+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.handle+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';
				obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-handle="'+_.handle+'" data-elementtype="" class="material-icons ol_link_to_add">add</i></div>');
				if (_.img!==undefined) {
					jQuery.get(_.img, function(data) {
						  var div = document.createElement("div");
						  div.className="ol_svg_preview";
						  div.innerHTML = new XMLSerializer().serializeToString(data.documentElement);
						  iwrap.append(div);
					});

				}
				iwrap[0].className += " patternbg";
			break;
			case "fonticons":
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content += '	<div class="olibrary_content_type oc_green">'+RVS_LANG.iconBIG+'</div>';
				content += '	<div class="olibrary_content_info oc_gray">'+RVS_LANG.fontBIG+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.handle+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';
				obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-handle="'+_.handle+'" data-elementtype="" class="material-icons ol_link_to_add">add</i></div>');
				var iconclassext = "";

				if (_.classextension!==undefined) {
					for (var i in _.classextension) {
						if(!_.classextension.hasOwnProperty(i)) continue;
						iconclassext += " "+_.classextension[i];
					}
				}


				if (_.tags[0]==="MaterialIcons")
					iwrap.append('<i class="fonticonobj material-icons">'+_.handle.replace(".","")+'</i>');
				else
					iwrap.append('<i class="fonticonobj '+iconclassext+' '+_.handle.replace(".","")+'"></i>');
				iwrap[0].className += " patternbg";
			break;

			case "modules":
				let favorites = typeof RS_SHORTCODE_FAV !== 'undefined' && RS_SHORTCODE_FAV.modules ? RS_SHORTCODE_FAV.modules : false;
				if(favorites) {
					for(let fav in favorites) {
						if(!favorites.hasOwnProperty(fav)) continue;
						if(favorites[fav] === _.id) {
							_.favorite = true;
							break;
						}
					}
				}

				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				if (_.folder)
					content += '	<div class="olibrary_content_type oc_package">'+RVS_LANG.folderBIG+'</div>';
				else
					content += '	<div class="olibrary_content_type oc_purple">'+RVS_LANG.moduleBIG+'</div>';
				if (!_.folder) content += '	<div class="olibrary_content_info oc_gray">'+_.type+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.id+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';

				if (_.folder) {
					obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-folderid="'+_.id+'" data-elementtype="folder_parent" data-title="'+_.title+'" data-packageid="'+_.package_id+'" class="material-icons ol_link_to_deeper">folder</i></div>');
					for (var i=1;i<=4;i++) {
						var sio = jQuery('<div class="folder_img_placeholder folder_img_'+i+'"></div>');
						if (_.children!==undefined && _.children.length>=i) {

							// IT HAS CHILDREN
							var _childindex = RVS.F.getSliderIndex(_.children[i-1]);
							if (_childindex!==-1) setObjBg(RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][_childindex],sio);
						}
						iwrap.append(sio);
					}
					iwrap.addClass("obj_med_darkbg");
				}
				else {
					if (RVS.LIB.OBJ.context==="editor") {
						obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-moduleid="'+_.id+'" data-folderid="'+_.id+'" data-elementtype="module_parent" data-title="'+_.title+'" data-packageid="'+_.id+'" class="material-icons ol_link_to_deeper">burst_mode</i></div>');
					}
					else {
						/*
						 * RVS.LIB.OBJ.shortcode_generator will equal true for the Gutenberg wizard
						*/
						if(!RVS.LIB.OBJ.shortcode_generator) {
							obj.append('<div class="olibrary_media_overlay"><i data-librarytype="'+_.libraryType+'" data-elementtype="" class="material-icons ol_link_to_add">add</i><i data-librarytype="'+_.libraryType+'" data-elementtype="" data-preview="'+_.preview+'"  class="material-icons ol_link_to_view">visibility</i></div>');
						}
						else {
							obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-elementtype="" class="material-icons ol_link_to_add">add</i></div>');
						}
					}
					setObjBg(_,iwrap);
				}



			break;

			case "objects":
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content += '	<div class="olibrary_content_type oc_cyan">'+RVS_LANG.objectBIG+'</div>';
				content += '	<div data-w="'+_.width+'" data-h="'+_.height+'" id="sizeinfo_'+_.libraryType+'_'+_.id+'" class="olibrary_content_info oc_gray">'+_.width+'x'+_.height+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.id+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';

				if (RVS.ENV.activated===false)
					obj.append('<div class="olibrary_media_overlay"><div class="avtivationicon"><i class="material-icons">not_interested</i>'+RVS_LANG.licencerequired+'</div></div>');
				else
					obj.append('<div class="olibrary_media_overlay"><div class="olibrary_addimage_wrapper"><div data-id="'+_.id+'" data-size="xs" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">xs</div><div data-id="'+_.id+'" data-size="s" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">s</div><div data-id="'+_.id+'" data-size="m" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">m</div><div data-id="'+_.id+'" data-size="l" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">l</div><div data-id="'+_.id+'" data-size="o" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">o</div></div></div>');
				if (_.img!==undefined && jQuery.type(_.img)==="string") {
					var imgobj = jQuery('<img class="olib_png_obj" src="'+_.img+'">');
					iwrap.append(imgobj);
				} else
				if (_.img!==undefined && jQuery.type(_.img)==="object") {
					var imgobj = _.img.style!==undefined ? jQuery('<div class="olibrary_media_style" style="'+_.img.style+'"></div>') : jQuery('<div class="olibrary_media_style"></div>');
					if (_.img.url!==undefined && _.img.url.length>3)  tpGS.gsap.set(imgobj,{backgroundImage:"url("+_.img.url+")", backgroundRepeat:"no-repeat","background-size":"contain", backgroundPosition:"center center"});
					iwrap.append(imgobj);

				}
				iwrap[0].className += " patternbg";
			break;
			case "images":
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content += '	<div class="olibrary_content_type oc_blue">'+RVS_LANG.imageBIG+'</div>';
				content += '	<div data-w="'+_.width+'" data-h="'+_.height+'" id="sizeinfo_'+_.libraryType+'_'+_.id+'" class="olibrary_content_info oc_gray">'+_.width+'x'+_.height+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.id+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';
				if (RVS.ENV.activated===false)
					obj.append('<div class="olibrary_media_overlay"><div class="avtivationicon"><i class="material-icons">not_interested</i>'+RVS_LANG.licencerequired+'</div></div>');
				else
					obj.append('<div class="olibrary_media_overlay"><div class="olibrary_addimage_wrapper"><div data-id="'+_.id+'" data-size="xs" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">xs</div><div data-id="'+_.id+'" data-size="s" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">s</div><div data-id="'+_.id+'" data-size="m" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">m</div><div data-id="'+_.id+'" data-size="l" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">l</div><div data-id="'+_.id+'" data-size="o" data-librarytype="'+_.libraryType+'" class="ol_link_to_add_image">o</div></div></div>');

				if (_.img!==undefined && jQuery.type(_.img)==="string") tpGS.gsap.set(iwrap,{backgroundImage:'url('+_.img+')', "background-repeat":"no-repeat", "background-size":"cover", backgroundPosition:"center center", backgroundRepeat:"no-repeat"});
				else
				if (_.img!==undefined && jQuery.type(_.img)==="object") {
					var imgobj = _.img.style!==undefined ? jQuery('<div class="olibrary_media_style" style="'+_.img.style+'"></div>') : jQuery('<div class="olibrary_media_style"></div>');
					if (_.img.url!==undefined && _.img.url.length>3)  tpGS.gsap.set(imgobj,{backgroundImage:"url("+_.img.url+")"});
					iwrap.append(imgobj);
				}
					iwrap[0].className += " patternbg";
			break;

			case "videos":
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content += '	<div class="olibrary_content_type oc_blue">'+RVS_LANG.videoBIG+'</div>';
				content += '	<div class="olibrary_content_info oc_gray">'+_.width+'x'+_.height+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.id+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';
				infocontent += '<div class="ol_template_info_wrap videopreview">';
				infocontent += '</div>';

				obj[0].className += " show_video_on_hover";
				obj[0].dataset.videosource=_.video_thumb.url;

				if (RVS.ENV.activated===false)
					obj.append('<div class="olibrary_media_overlay"><div class="avtivationicon"><i class="material-icons">not_interested</i>'+RVS_LANG.licencerequired+'</div></div>');
				else
					obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-id="'+_.id+'" data-handle="'+_.handle+'" data-elementtype="" class="material-icons ol_link_to_add">add</i></div>');


				iwrap[0].dataset.videosource=_.video_thumb.url;
				if (_.img!==undefined && jQuery.type(_.img)==="string") tpGS.gsap.set(iwrap,{backgroundImage:'url('+_.img+')', "background-repeat":"no-repeat", "background-size":"cover", backgroundPosition:"center center"});
				else
				if (_.img!==undefined && jQuery.type(_.img)==="object") {
					var imgobj = _.img.style!==undefined ? jQuery('<div class="olibrary_media_style" style="'+_.img.style+'"></div>') : jQuery('<div class="olibrary_media_style"></div>');
					if (_.img.url!==undefined && _.img.url.length>3)  tpGS.gsap.set(imgobj,{backgroundImage:"url("+_.img.url+")"});
					iwrap.append(imgobj);
				}
			break;

			case "layers":
				_.title = RVS.F.capitaliseAll(_.title.replace(/[_-]/g,' '));
				content = '<div class="olibrary_content_left">';
				content += '	<div class="olibrary_content_title">'+_.title+'</div>';
				content += '	<div class="olibrary_content_type oc_blue">'+RVS_LANG.layersBIG+'</div>';
				content += '	<div class="olibrary_content_info oc_gray">'+_.width+'x'+_.height+'</div>';
				content += '</div>';
				content += '<div class="olibrary_content_right">';
				content += '	<i data-id="'+_.id+'" data-type="'+_.type+'" data-libraryType="'+_.libraryType+'" class="olibrary_favorit material-icons '+(_.favorite?"selected" : "")+'">star</i>';
				content += '</div>';
				infocontent += '<div class="ol_template_info_wrap videopreview">';
				infocontent += '</div>';

				obj[0].className += " show_video_on_hover";
				obj[0].dataset.videosource=_.video_thumb.url;

				if (RVS.ENV.activated===false)
					obj.append('<div class="olibrary_media_overlay"><div class="avtivationicon"><i class="material-icons">not_interested</i>'+RVS_LANG.licencerequired+'</div></div>');
				else
					obj.append('<div class="olibrary_media_overlay oneicon"><i data-librarytype="'+_.libraryType+'" data-id="'+_.id+'" data-handle="'+_.handle+'" data-elementtype="" class="material-icons ol_link_to_add">add</i></div>');


				iwrap[0].dataset.videosource=_.video_thumb.url;
				if (_.img!==undefined && jQuery.type(_.img)==="string") tpGS.gsap.set(iwrap,{backgroundImage:'url('+_.img+')', "background-repeat":"no-repeat", "background-size":"cover", backgroundPosition:"center center"});
				else
				if (_.img!==undefined && jQuery.type(_.img)==="object") {
					var imgobj = _.img.style!==undefined ? jQuery('<div class="olibrary_media_style" style="'+_.img.style+'"></div>') : jQuery('<div class="olibrary_media_style"></div>');
					if (_.img.url!==undefined && _.img.url.length>3)  tpGS.gsap.set(imgobj,{backgroundImage:"url("+_.img.url+")", backgroundSize:"cover"});
					iwrap.append(imgobj);
				}
			break;
		}


		if (content!=="") cwrap.append(content);
		if (infocontent!=="") obj.append(infocontent);

		return obj;
	};


	function setObjBg(_,imgobj) {
		var	imgsrc = _.bg.src!==undefined && _.bg.src.length>3 ? _.bg.src : RVS.ENV.plugin_url+'admin/assets/images/sources/'+_.source+".png",
			styl = _.bg.style!==undefined ? _.bg.style : {};
			if (Array.isArray(styl)) styl = RVS.F.toObject(styl);
		switch (_.bg.type) {
			case "image":
				styl.backgroundImage = "url("+imgsrc+")";
				tpGS.gsap.set(imgobj,styl);
			break;
			case "color":
			case "colored":
			case "solid":
				var colval = window.RSColor.get(styl["background-color"]);
				if (colval.indexOf("gradient")>=0)
					tpGS.gsap.set(imgobj,{backgroundImage:colval});
				else
					tpGS.gsap.set(imgobj,{backgroundColor:colval});
			break;
			case "transparent":
				tpGS.gsap.set(imgobj,{backgroundImage:"url("+RVS.ENV.plugin_url+'admin/assets/images/sources/'+(_.source===undefined ? "gallery" : _.source)+".png)", backgroundRepeat:"no-repeat", backgroundSize:"cover"});
			break;
		}
	}

	RVS.F.changeOLIBToFolder = function(folder) {
		RVS.LIB.OBJ.selectedFolder = folder;
		RVS.F.resetAllFilters();
		RVS.F.updateFilteredList({force:true,keeppage:false,noanimation:false});
	}


	/*
	BUILD THE PAGINATION BASED ON THE CURRENT FILTERS
	*/
	RVS.F.buildPagination = function(_) {

		var maxamount,
			extender,
			dbl,
			cpage = RVS.F.getCookie("rs6_library_pagination");

		maxamount = extender = dbl = getMaxItemOnPage();
		jQuery('#ol_right').scrollTop(0);
		_ = _===undefined ? {keeppage:false} : _;

		// REBUILD PAGINATION DROPDOWN
		if (RVS.LIB.OBJ.maxAmountPerPage!==maxamount) {
			jQuery('#ol_pagination').select2RS('destroy');
			RVS.LIB.OBJ.maxAmountPerPage=maxamount;
			for (var i=0;i<=4;i++) {
				var opt = document.getElementById('olpage_per_page_'+i);
				opt.value = dbl;
				opt.selected = (opt.value===cpage);
				opt.innerHTML = RVS_LANG.show+" "+dbl+" "+RVS_LANG.perpage;
				dbl = dbl + extender;
			}
			jQuery('#ol_pagination').select2RS({
				minimumResultsForSearch:"Infinity"
			});
		}


		if (RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].length<maxamount) {
			RVS.LIB.OBJ.container_Library.find('#ol_footer').hide();
		//	RVS.LIB.OBJ.container_Pagination.val("all");
		} else {

			RVS.LIB.OBJ.container_Library.find('#ol_footer').show();
		}


		RVS.LIB.OBJ.selectedPage = !_.keeppage ? 1 : jQuery('.page_button.ol_pagination.selected').length>0 ? jQuery('.page_button.ol_pagination.selected').data('page') : 1;

		var a = RVS.LIB.OBJ.container_Pagination.select2RS('data')[0] === undefined ? 4 :RVS.LIB.OBJ.container_Pagination.select2RS('data')[0].id,
			counter = 0;

		RVS.LIB.OBJ.pageAmount = a==="all" || parseInt(a,0)===null || parseInt(a,0)===0 ? 1 : Math.ceil(RVS.LIB.OBJ.filteredList.length / parseInt(a,0));
		RVS.LIB.OBJ.itemPerPage = a === "all" ? 99999 : parseInt(a,0);
		RVS.LIB.OBJ.itemPerPage = RVS.LIB.OBJ.selectedFolder!=-1 ? RVS.LIB.OBJ.itemPerPage-1 : RVS.LIB.OBJ.itemPerPage;
		RVS.LIB.OBJ.container_PaginationWrap[0].innerHTML = "";
		var sel;
		RVS.LIB.OBJ.selectedPage = RVS.LIB.OBJ.selectedPage>RVS.LIB.OBJ.pageAmount ? RVS.LIB.OBJ.pageAmount : RVS.LIB.OBJ.selectedPage;


		// BUILD THE PAGINATION BUTTONS
		if (RVS.LIB.OBJ.pageAmount>1){
			for (var i=1;i<=RVS.LIB.OBJ.pageAmount;i++) {

				sel = i!==RVS.LIB.OBJ.selectedPage ? "" : "selected";
				RVS.LIB.OBJ.container_PaginationWrap[0].innerHTML += '<div data-page="'+i+'" class="'+sel+' page_button ol_pagination">'+i+'</div>';
				if (i===1)
					RVS.LIB.OBJ.container_PaginationWrap[0].innerHTML += '<div data-page="-9999" class="page_button ol_pagination">...</div>';
				else
				if (i===RVS.LIB.OBJ.pageAmount-1)
					RVS.LIB.OBJ.container_PaginationWrap[0].innerHTML += '<div data-page="9999" class="page_button ol_pagination">...</div>';
			}
		}
		// BUILD THE PAGES LIST
		RVS.LIB.OBJ.pages = [];
		RVS.LIB.OBJ.pages.push([]);

		for (var f in RVS.LIB.OBJ.filteredList) {

			if(!RVS.LIB.OBJ.filteredList.hasOwnProperty(f)) continue;

			RVS.LIB.OBJ.pages[RVS.LIB.OBJ.pages.length-1].push(RVS.LIB.OBJ.filteredList[f]);
			counter++;
			if (counter===RVS.LIB.OBJ.itemPerPage) {
				counter = 0;
				RVS.LIB.OBJ.pages.push([]);
			}
		}

		smartPagination();


	};



	// BUILD THE OBJECT LIBRARY
	RVS.F.buildObjectLibrary = function(hideUpdateBtn) {
		var _html = '<div id="objectlibrary" class="rs_overview _TPRB_">';
		_html +='	<div class="rb_the_logo">SR</div>';
		_html += '	<div id="ol_filters_wrap">';
		_html += '		<div id="ol_filters"></div>';
		_html +='	</div>';
		_html +='	<div id="ol_right">';
		_html +='		<div id="ol_header" class="overview_header_footer">';
		_html +='				<div class="rs_fh_left"><input class="flat_input" id="searchobjects" type="text" placeholder="Search Modules ..."></div>';
		_html +='				<div class="rs_fh_right">';
		_html +=' 					<div id="obj_fil_favorite"><i class="material-icons">star</i>'+RVS_LANG.ol_favorite+'</div>';
		_html +='					<div id="ol_modulessorting"><i class="material-icons reset_select" id="reset_objsorting">replay</i><select id="sel_olibrary_sorting" data-evt="updateObjectLibraryOverview" data-evtparam="#reset_objsorting" class="overview_sortby tos2 nosearchbox callEvent" data-theme="autowidth" tabindex="-1" aria-hidden="true"><option value="datedesc">'+RVS_LANG.sortbycreation+'</option><option value="date">'+RVS_LANG.creationascending+'</option><option value="title">'+RVS_LANG.sortbytitle+'</option><option value="titledesc">'+RVS_LANG.titledescending+'</option></select></div>';
		if(!hideUpdateBtn)
			_html +=' 				<div id="obj_updatefromserver"><i class="material-icons">update</i>'+RVS_LANG.updatefromserver+'</div>';
		_html +=' 					<div id="obj_addsliderasmodal">'+RVS_LANG.sliderasmodal+'<input id="obj_addsliderasmodal_input" data-r="modal" class="scblockinput" type="checkbox" value="off"></div>';
		_html +='					<i id="ol_close" class="material-icons">close</i>';
		_html +='				</div>';
		_html +='				<div class="tp-clearfix"></div>';
		_html +='		</div>';
		_html +='		<div id="ol_results_wrap">';
		_html +=' 			<div id="ol_right_underlay"></div>';
		_html +='			<div id="ol_results"></div>';
		_html +='		</div>';
		_html +='		<div id="ol_footer" class="overview_header_footer">';
		_html +='			<div class="rs_fh_left"><div id="rs_copyright">'+RVS_LANG.copyrightandlicenseinfo+'</div></div>';
		_html +='			<div class="rs_fh_right"><div id="ol_pagination_wrap" class="ol-pagination"></div>';
		_html +='				<select id="ol_pagination" data-evt="updateObjectLibraryOverview" class="overview_pagination tos2 nosearchbox callEvent" data-theme="nomargin"><option id="olpage_per_page_0" selected="selected" value="4"></option><option id="olpage_per_page_1"  value="8"></option><option id="olpage_per_page_2" value="16"></option><option id="olpage_per_page_3" value="32"></option><option id="olpage_per_page_4" value="64"></option><option value="all">Show All</option></select>';
		_html +='			</div>';
		_html +='			<div class="tp-clearfix"></div>';
		_html +='		</div>';
		_html +='	</div>';
		_html += '</div>';

		RVS.LIB.OBJ.container_Library = jQuery(_html);
		RVS.LIB.OBJ.container_Underlay = RVS.LIB.OBJ.container_Library.find('#ol_right_underlay');
		RVS.LIB.OBJ.container_Right = RVS.LIB.OBJ.container_Library.find('#ol_right');
		RVS.LIB.OBJ.container_Filters = RVS.LIB.OBJ.container_Library.find('#ol_filters');
		RVS.LIB.OBJ.container_Output = RVS.LIB.OBJ.container_Library.find('#ol_results');
		RVS.LIB.OBJ.container_OutputWrap = RVS.LIB.OBJ.container_Library.find('#ol_results_wrap');
		RVS.LIB.OBJ.container_PaginationWrap = RVS.LIB.OBJ.container_Library.find('#ol_pagination_wrap');
		RVS.LIB.OBJ.container_Pagination = RVS.LIB.OBJ.container_Library.find('#ol_pagination');
		RVS.LIB.OBJ.container_Sorting = RVS.LIB.OBJ.container_Library.find('#ol_modulessorting');


		//addObjectFilter({groupType:"favorite", groupAlias:RVS_LANG['ol_favorite'],icon:"star", tags:{}});
		for (var types in RVS.LIB.OBJ.types) {
			if(!RVS.LIB.OBJ.types.hasOwnProperty(types)) continue;
			addObjectFilter({groupType:types, groupAlias:RVS_LANG['ol_'+types], icon:filtericons[types], count:RVS.LIB.OBJ.types[types].count, tags:RVS.LIB.OBJ.types[types].tags});
		}

		jQuery('body').append(RVS.LIB.OBJ.container_Library);
		// INITIALISE SELECTBOXES
		jQuery('#sel_olibrary_sorting').select2RS({minimumResultsForSearch:"Infinity"});
		jQuery('#ol_pagination').select2RS({minimumResultsForSearch:"Infinity"});
		updateScollbarFilters();
	};





	/*
	LOCAL LISTENERS
	*/
	function initLocalListeners() {

		// CLICK ON CLOSE SHOULD ANIMATE OL OUT
		RVS.DOC.on('click','#ol_close',function() {
			if (RVS.LIB.OBJ.moduleInFocus === true) {
				unselectOLItems();
				RVS.LIB.OBJ.moduleInFocus = false;
			} else
				RVS.F.closeObjectLibrary();
		});

		// RESET THE SORTING
		RVS.DOC.on('click','#reset_objsorting',function() {
			unselectOLItems();
			jQuery('#sel_olibrary_sorting').val("datedesc").trigger('change.select2RS');
			RVS.DOC.trigger('updateObjectLibraryOverview',{val:"datedesc", eventparam:"#reset_objsorting",ignoreCookie:true});
		});

		//UPDATE SLIDER OVERVIEW
		RVS.DOC.on('updateObjectLibraryOverview',function(e,p) {

			if (p!==undefined && p.eventparam!==undefined) {
				var a = p.eventparam === "#reset_objsorting" ? p.val==="datedesc" ? 0 : 1 : p.val==="all" ? 0 : 1,
					d = a ===1 ? "inline-block" : "none";

				tpGS.gsap.set(p.eventparam,{autoAlpha:a, display:d});
			}
			if (p!==undefined && !p.ignoreRebuild) {
				//hideElementSubMenu({keepOverlay:false});
				if (p.val!==undefined && p.ignoreCookie!==true) RVS.F.setCookie("rs6_library_pagination",p.val,360);
				unselectOLItems();
				RVS.F.updateFilteredList({force:true,keeppage:false,noanimation:false});
			}
		});

		//CLICK ON LISTELEMENT SHOULD LOAD THE NEXT LIBRARY
		RVS.DOC.on('click','.ol_filter_listelement',function() {
			var _t = jQuery(this),
				_c = _t.closest('.ol_filter_type');

			if (this.dataset.subtags!="true") {
				RVS.LIB.OBJ.lastSelectedType = RVS.LIB.OBJ.selectedType;
				RVS.LIB.OBJ.selectedType = this.dataset.type;
				RVS.LIB.OBJ.selectedFilter = this.dataset.filter;
				RVS.LIB.OBJ.selectedPage = 1;
				RVS.LIB.OBJ.selectedPackage = -1;
				RVS.LIB.OBJ.selectedFolder = -1;
				RVS.F.loadLibrary({modules:[this.dataset.type],event:"reBuildObjectLibrary"});
				jQuery('.ol_filter_listelement.selected').removeClass("selected");
				_t.addClass("selected");
				_c.find('.ol_filter_headerelement').addClass("selected");
			} else {
				var _w = _c.hasClass("open");
				jQuery('.ol_filter_type.open').removeClass("open");
				if (!_w) _c.addClass("open");
				var ul = _c.find('.ol_filter_group');
				if (ul.find('.selected').length===0) ul.find('.ol_filter_listelement').first().click();
			}

			updateSearchPlaceHolder();
			unselectOLItems();
			return false;
		});

		RVS.DOC.on('click','#ol_right_underlay',unselectOLItems);

		//CLICK ON THE LINK_TO_ADD BUTTON
		RVS.DOC.on('click','.ol_link_to_add',function() {

			var librarytype = this.dataset.librarytype;

			// activation check not needed for post/page shortcode wizard
			if(librarytype !== 'modules') {

				if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) {
					RVS.F.showRegisterSliderInfo();
					return;
				}

			}

			switch (librarytype) {

				// for page & post shortcode wizard
				case 'modules':

					let ids = jQuery(this).closest('.olibrary_item').attr('data-objid'),
						modules = RVS.LIB.OBJ.items.modules,
						len = modules.length,
						itm;

					for(let i = 0; i < len; i++) {

						itm = modules[i];
						if(itm.id === ids) break;

					}

					RVS.DOC.trigger(RVS.LIB.OBJ.success.modules, itm);
					RVS.F.closeObjectLibrary();

				break;

				case "moduleslides":
				case "moduletemplateslides":


					var _ = RVS.LIB.OBJ.items[this.dataset.librarytype][RVS.F.getModuleIndex(this.dataset.id,this.dataset.librarytype)];
					if (_.installed==undefined) {
						var puid = this.dataset.parentuid;
						/* id = this.dataset.id; */

						RVS.F.ajaxRequest('import_template_slider', {uid:puid}, function(response){
							if (response.success) {
								setModuleTemplateInstalled({uid:puid,hiddensliderid:response.hiddensliderid,children:true,slideids:response.slider.slide_ids});
								RVS.DOC.trigger(RVS.LIB.OBJ.success.slide,_.slideid);
								RVS.F.closeObjectLibrary();
							}
						},undefined,undefined,RVS_LANG.installingtemplate+'<br><span style="font-size:17px; line-height:25px;">'+this.dataset.parenttitle+'</span>');
					} else {
						RVS.DOC.trigger(RVS.LIB.OBJ.success.slide,_.slideid);
						RVS.F.closeObjectLibrary();
					}
				break;
				case "moduletemplates":
					RVS.LIB.OBJ.container_Underlay.show();
					RVS.LIB.OBJ.moduleInFocus = true;
					var _ = jQuery(this);
					if (this.dataset.librarytype==="moduletemplates") {
						var item = _.closest('.olibrary_item'),
							info = item.find('.ol_template_info_wrap');
						item.addClass("selected");
						var l = item.offset().left;
						tpGS.gsap.set(info,{left:"auto",right:"auto"});

						if (l+630 > (window.outerWidth + (RVS.S.isRTL ? -300 : 0)))
							if ((l-340) > 300)
								tpGS.gsap.set(info,{left:"auto", right:"100%", x:"-20px", transformOrigin: "100% 0%"});
							else
								 tpGS.gsap.set(info,{left:(item.width() - ((l+630)-window.outerWidth))+"px", zIndex:200, right:"auto", x:"20px", transformOrigin: "0% 0%"});
						else
							 tpGS.gsap.set(info,{left: "100%", right: "auto", x:"20px", transformOrigin: "0% 0%"});

						var rts = item.find('.redownloadTemplateState'),
							cbp = item.find('.createBlankPageState');

						if (rts.length>0) rts[0].checked = RVS.LIB.OBJ.reDownloadTemplate;
						if (cbp.length>0) cbp[0].checked = RVS.LIB.OBJ.createBlankPage;

						RVS.F.turnOnOffVisUpdate({input:rts});
						RVS.F.turnOnOffVisUpdate({input:cbp});
					}
				break;
				case "videos":
					var response_basic = RVS.F.safeExtend(true,RVS.LIB.OBJ.data,getObjByID(this.dataset.id, this.dataset.librarytype));
					RVS.F.ajaxRequest('load_library_object', {type:"video",id:this.dataset.id}, function(response){
						if (response.success) {
							response_basic.img =  response.cover;
							response_basic.video = response.url;
							RVS.DOC.trigger(RVS.LIB.OBJ.success.video,response_basic);
						}
					});
					RVS.F.closeObjectLibrary();
				break;

				case "layers":

					RVS.F.ajaxRequest('load_library_object', {type:"layers",id:this.dataset.id}, function(response){
						if (response.success) {
							//var _IL = jQuery.parseJSON(response.layers);
							RVS.LIB.OBJ.import = {toImport :[]};
							for (var i in response.layers) {
								if(!response.layers.hasOwnProperty(i)) continue;
								RVS.LIB.OBJ.import.toImport.push(response.layers[i].uid);
							}
							RVS.F.showWaitAMinute({fadeIn:100,text:RVS_LANG.importinglayers});
							RVS.F.importSelectedLayers(response.layers);
							RVS.DOC.trigger(RVS.LIB.OBJ.success.layers);
						} else {
							RVS.F.closeObjectLibrary();
						}
					});

				break;
				case "fonticons":
				case "svgs":
					var cbobj =getObjByHandle(this.dataset.handle, this.dataset.librarytype);
					if (this.dataset.librarytype==="svgs")
						cbobj.path = cbobj.ref.find('svg path').attr('d');

					RVS.DOC.trigger(RVS.LIB.OBJ.success.icon,cbobj);
					RVS.F.closeObjectLibrary();
				break;
				default:
				break;
			}
		});

		RVS.DOC.on('mouseenter','.ol_link_to_add_image',function() {
			var sizeInfo = document.getElementById('sizeinfo_'+this.dataset.librarytype+'_'+this.dataset.id);
			if (sizeInfo!==null && sizeInfo!==undefined)
				sizeInfo.innerHTML = Math.round(parseInt(sizeInfo.dataset.w,0) * (objectSizes[this.dataset.size]/100)) + "x" + Math.round(parseInt(sizeInfo.dataset.h,0) * (objectSizes[this.dataset.size]/100));

		});

		RVS.DOC.on('mouseleave','.ol_link_to_add_image',function() {
			var sizeInfo = document.getElementById('sizeinfo_'+this.dataset.librarytype+'_'+this.dataset.id);
			if (sizeInfo!==null && sizeInfo!==undefined) sizeInfo.innerHTML = parseInt(sizeInfo.dataset.w,0)  + "x" + parseInt(sizeInfo.dataset.h,0);
		});

		RVS.DOC.on('click','.ol_link_to_add_image',function() {
			if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) {
				RVS.F.showRegisterSliderInfo();
				return;
			}
			var response_basic = RVS.F.safeExtend(true,RVS.LIB.OBJ.data,getObjByID(this.dataset.id, this.dataset.librarytype));
			response_basic.size = objectSizes[this.dataset.size];
			RVS.F.ajaxRequest('load_library_object', {type:objectSizes[this.dataset.size],id:this.dataset.id}, function(response){
				if (response.success) {
					response_basic.img = response.url;
					RVS.DOC.trigger(RVS.LIB.OBJ.success.image,response_basic);
				}
			});
			RVS.F.closeObjectLibrary();
		});

		//EVENT CALL TO REDRAW THE LIBRARY STRUCTURE BASED ON ITEMS, SORT, ETC.
		RVS.DOC.on('reBuildObjectLibrary',function() {
			//BUILD THE PAGINATION HERE
			unselectOLItems();
			RVS.F.reBuildObjectLibrary();
			jQuery('.ol_filter_type.selected').removeClass("selected");
			jQuery('.ol_filter_listelement.selected').removeClass("selected");
			jQuery('.ol_filter_listelement').each(function() {
				if (this.dataset.filter === RVS.LIB.OBJ.selectedFilter && this.dataset.type === RVS.LIB.OBJ.selectedType) this.classList.add("selected");
			});
			jQuery('.ol_filter_type.open').addClass("selected");
		});

		RVS.DOC.on('reBuildObjectLibraryAndCheckSingleSlide',function() {
			//BUILD THE PAGINATION HERE
			unselectOLItems();
			RVS.F.reBuildObjectLibrary();
			// CHECK IF ONLY 1 SLIDE EXISTS....
			var count = 0,firstid,installed;
			for (var i in RVS.LIB.OBJ.items.moduleslides) {
				if(!RVS.LIB.OBJ.items.moduleslides.hasOwnProperty(i)) continue;
				if(RVS.LIB.OBJ.items.moduleslides[i].slider_id===RVS.LIB.OBJ.selectedModule) {
					count++;
					firstid =  RVS.LIB.OBJ.items.moduleslides[i].id;
					installed = RVS.LIB.OBJ.items.moduleslides[i].installed;
				}
			}

			if (count===1 && RVS.LIB.OBJ.depth==="layers") enterInModuleSlide(firstid, installed);
		});


		// SHOW CONTENT OF OBJECT LIBRARY ELEMENT
		RVS.DOC.on('click','.ol_link_to_view',function() {
			var _ = jQuery(this);
			if (_[0].dataset.preview!==undefined && _[0].dataset.preview.length>0) window.open(_[0].dataset.preview,'_blank');
		});


		RVS.DOC.on('mouseenter','.show_video_on_hover',function() {
			clearTimeout(window.showVideOnHoverTimer);
			var _ = jQuery(this),
				item = _.closest('.olibrary_item'),
				info = item.find('.ol_template_info_wrap'),
				src = this.dataset.videosource;
			window.showVideOnHoverTimer = setTimeout(function() {
				item.find('.videopreview').append('<video id="obj_library_mediapreview" loop autoplay> <source src="'+src+'" type="video/mp4"></video>');
				item.addClass("selected");

				var l = item.offset().left;
				tpGS.gsap.set(info,{left:"auto",right:"auto"});
				if (l+630 > (window.outerWidth + (RVS.S.isRTL ? -300 : 0)))
					if ((l-340) > 300)
						tpGS.gsap.set(info,{left:"auto", right:"100%", x:"-20px", transformOrigin: "100% 0%"});
					else
						 tpGS.gsap.set(info,{left:(item.width() - ((l+630)-window.outerWidth))+"px", zIndex:200, right:"auto", x:"20px", transformOrigin: "0% 0%"});
				else
					 tpGS.gsap.set(info,{left: "100%", right: "auto", x:"20px", transformOrigin: "0% 0%"});
			},500);
		});

		RVS.DOC.on('mouseleave','.show_video_on_hover',function() {
			clearTimeout(window.showVideOnHoverTimer);
			unselectOLItems();
		});

		// GET INTO A PACKAGE
		RVS.DOC.on('click','.ol_link_to_deeper',function() {

			RVS.LIB.OBJ.selectedModule = -1;
			RVS.LIB.OBJ.selectedModuleTitle = "";
			jQuery('#searchobjects').val("");


			if (this.dataset.librarytype==="moduletemplates") {
				if (this.dataset.elementtype==="package_parent") {
					RVS.LIB.OBJ.selectedPackage = this.dataset.packageid;
					RVS.LIB.OBJ.selectedPackageTitle = this.dataset.title;
					unselectOLItems();

					RVS.F.updateFilteredList({force:true,keeppage:false,noanimation:false});
				} else

				if (this.dataset.elementtype==="module_parent") {
					//LOAD ITEMS AND CALL FURTHER FUNCTIONS
					RVS.LIB.OBJ.lastSelectedType = RVS.LIB.OBJ.selectedType;
					RVS.LIB.OBJ.selectedModule = this.dataset.packageid;
					RVS.LIB.OBJ.selectedModuleTitle = this.dataset.title;
					RVS.LIB.OBJ.selectedType = "moduletemplateslides";
					RVS.F.loadSimpleModule({modules:["moduletemplateslides"], parenttype:"moduletemplates", moduleid:this.dataset.moduleid, module_uid:this.dataset.module_uid, event:"reBuildObjectLibrary"});
				}

			} else
			if (this.dataset.librarytype==="modules") {
				if (this.dataset.elementtype==="folder_parent") {

					RVS.LIB.OBJ.selectedFolder = this.dataset.folderid;
					RVS.F.resetAllFilters();
					RVS.F.updateFilteredList({force:true,keeppage:false,noanimation:false});
				} else
				if (this.dataset.elementtype==="module_parent") {

					//LOAD ITEMS AND CALL FURTHER FUNCTIONS
					RVS.LIB.OBJ.lastSelectedType = RVS.LIB.OBJ.selectedType;
					RVS.LIB.OBJ.selectedModule = this.dataset.packageid;
					RVS.LIB.OBJ.selectedModuleTitle = this.dataset.title;
					RVS.LIB.OBJ.selectedType = "moduleslides";
					RVS.F.loadSimpleModule({modules:["moduleslides"], parenttype:"modules", moduleid:this.dataset.moduleid, event:"reBuildObjectLibraryAndCheckSingleSlide"});
				}

			} else
			if (this.dataset.librarytype==="moduleslides") enterInModuleSlide(this.dataset.id, this.dataset.slideid);

			jQuery('#ol_right').scrollTop(0);
		});

		//PAGINATION TRIGGER
		RVS.DOC.on('click','.page_button.ol_pagination',function() {
			unselectOLItems();
			jQuery('.page_button.ol_pagination.selected').removeClass('selected');
			RVS.LIB.OBJ.selectedPage = parseInt(this.dataset.page,0)===-9999 ? RVS.LIB.OBJ.selectedPage = parseInt(RVS.LIB.OBJ.selectedPage,0)-3 : parseInt(this.dataset.page,0)===9999 ? RVS.LIB.OBJ.selectedPage = parseInt(RVS.LIB.OBJ.selectedPage,0)+3 : this.dataset.page;
			jQuery('.page_button.ol_pagination[data-page='+RVS.LIB.OBJ.selectedPage+']').addClass("selected");

			jQuery('#ol_right').scrollTop(0);
			RVS.F.drawOverview();
			smartPagination();
		});


		// RESIZE SCREEN
		RVS.WIN.on('resize',function() {
			if (RVS.LIB.OBJ.open) {
				clearTimeout(window.resizedObjectLibraryTimeOut);
				window.resizedObjectLibraryTimeOut = setTimeout(function() {
					var maxamount = getMaxItemOnPage();
					maxamount=maxamount<1 ? 1 : maxamount;
					unselectOLItems();
					if (RVS.LIB.OBJ.maxAmountPerPage!==maxamount)
						RVS.F.updateFilteredList({force:true,keeppage:true,noanimation:true});

				},10);
			}
		});

		// FOLLOW BREADCRUMB
		RVS.DOC.on('click','.rsl_breadcrumb',function() {
			RVS.LIB.OBJ.selectedModule = -1;
			RVS.LIB.OBJ.selectedModuleTitle = "";
			RVS.LIB.OBJ.selectedModuleType = "";
			RVS.LIB.OBJ.selectedType = RVS.LIB.OBJ.selectedType ==="moduletemplateslides" ? "moduletemplates" : RVS.LIB.OBJ.selectedType ==="moduleslides" ? "modules" : RVS.LIB.OBJ.selectedType;
			if (this.dataset.folderid!==undefined) {
				unselectOLItems();
				if (RVS.LIB.OBJ.selectedType==="moduletemplates")
					RVS.LIB.OBJ.selectedPackage = parseInt(this.dataset.folderid,0);
				if (RVS.LIB.OBJ.selectedType==="modules") {
					RVS.LIB.OBJ.selectedFolder = parseInt(this.dataset.folderid,0);
					RVS.F.resetAllFilters();
				}
				RVS.F.updateFilteredList({force:true,keeppage:true,noanimation:true});
			}

		});

		// ADD / REMOVE FROM FAVORIT LIST
		RVS.DOC.on('click','.olibrary_favorit',function() {
			var el = jQuery(this),
				par = {do:"add",type:this.dataset.librarytype, id:this.dataset.id};

			el.toggleClass('selected');
			if (!el.hasClass("selected")) par.do="remove";

			RVS.F.ajaxRequest('set_favorite', par, function(response){
				if (response.success) {
					setFavorite(par);
					RVS.F.updateFilteredList({force:true,keeppage:true,noanimation:true});
				}
			});

		});

		RVS.DOC.on('click','#obj_updatefromserver',function() {
			RVS.F.updateObjectLibraryFromServer(RVS.LIB.OBJ.selectedType);
		});

		//CLICK ON/OFF MAIN FAVORIT SWITCH
		RVS.DOC.on('click','#obj_fil_favorite',function(){
			var el = jQuery(this);
			el.toggleClass("selected");
			unselectOLItems();
			RVS.F.updateFilteredList({force:true,keeppage:true,noanimation:true});
		});

		// SEARCH MODULE TRIGGERING
		RVS.DOC.on('keyup','#searchobjects',function() {
			unselectOLItems();
			clearTimeout(window.searchKeyUp);
			window.searchKeyUp = setTimeout(function() {
				 RVS.F.updateFilteredList({force:true,keeppage:false,noanimation:false});
				 RVS.LIB.OBJ.container_OutputWrap.RSScroll("update");
			},200);
		});

		//CHANGE REDOWNLOAD STATE
		RVS.DOC.on('change','.redownloadTemplateState',function() {
			RVS.LIB.OBJ.reDownloadTemplate = this.checked;
		});

		//CHANGE REDOWNLOAD STATE
		RVS.DOC.on('change','.createBlankPageState',function() {
			RVS.LIB.OBJ.createBlankPage = this.checked;
		});



		//INSTALL A TEMPLATE
		RVS.DOC.on('click','.olti_install_template',function() {
			if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) {
				RVS.F.showRegisterSliderInfo();
				return;
			}

			var uid = this.dataset.uid,
				temp = getModuleTemplateByUID(uid);
			RVS.LIB.OBJ.sliderPackageIds = [];
			if (RVS.LIB.OBJ.reDownloadTemplate || temp.installed==false)
				RVS.F.ajaxRequest('import_template_slider', {uid:uid, install:true}, function(response){
					if (response.success) {
						RVS.LIB.OBJ.sliderPackageIds.push(response.slider.id);
						if (RVS.LIB.OBJ.success!==undefined && RVS.LIB.OBJ.success.slider!==undefined) RVS.DOC.trigger(RVS.LIB.OBJ.success.slider,response);
						if (RVS.LIB.OBJ.createBlankPage &&  RVS.LIB.OBJ.success && RVS.LIB.OBJ.success.draftpage) RVS.DOC.trigger(RVS.LIB.OBJ.success.draftpage,{pages:RVS.LIB.OBJ.sliderPackageIds});
						setModuleTemplateInstalled({uid:uid,hiddensliderid:response.hiddensliderid});
					}
					RVS.F.closeObjectLibrary();
				},undefined,undefined,RVS_LANG.installtemplate+'<br><span style="font-size:17px; line-height:25px;">'+this.dataset.title+'</span>');
			else
				RVS.F.ajaxRequest('install_template_slider', {uid:this.dataset.uid, sliderid:temp.installed}, function(response){
					if (response.success)
						RVS.LIB.OBJ.sliderPackageIds.push(response.slider.id);
						if (RVS.LIB.OBJ.success!==undefined && RVS.LIB.OBJ.success.slider!==undefined) RVS.DOC.trigger(RVS.LIB.OBJ.success.slider,response);
						if (RVS.LIB.OBJ.createBlankPage && RVS.LIB.OBJ.success && RVS.LIB.OBJ.success.draftpage) RVS.DOC.trigger(RVS.LIB.OBJ.success.draftpage,{pages:RVS.LIB.OBJ.sliderPackageIds});
					RVS.F.closeObjectLibrary();
				},undefined,undefined,RVS_LANG.installtemplate+'<br><span style="font-size:17px; line-height:25px;">'+this.dataset.title+'</span>');
		});

		//INSTALL A TEMPLATE
		RVS.DOC.on('click','.olti_install_template_package',function() {
			if (RVS.ENV.activated!=="true" && RVS.ENV.activated!==true) {
				RVS.F.showRegisterSliderInfo();
				return;
			}
			var uids = this.dataset.uids.split(","),
				folderuid = this.dataset.folderuid;
			RVS.F.createNewFolder({foldername:this.dataset.package, enter:true, callBack:'sliderPackageInstall', callBackParam:{uids:uids, index:0, folderuid:folderuid, name:this.dataset.package, createBlankPage: RVS.LIB.OBJ.createBlankPage, amount:(uids.length-1)}});
		});

		//TRIGGER SLIDER PACKAGE INSTALLATION
		RVS.DOC.on('sliderPackageInstall',function(e,par) {
			RVS.LIB.OBJ.sliderPackageIds = [];
			RVS.LIB.OBJ.sliderPackageReferenceMap = new Object();
			RVS.LIB.OBJ.sliderPackageReferenceMap.slider_map = new Object();
			RVS.LIB.OBJ.sliderPackageReferenceMap.slides_map = new Object();
			RVS.LIB.OBJ.sliderPackageModals = [];
			RVS.LIB.OBJ.sliderPackageModalsOrig = [];
			RVS.LIB.OBJ.sliderPackageModalsOrigUid = [];
			RVS.LIB.OBJ.sliderPackageModal = false;
			RVS.LIB.OBJ.sliderPackageAdditions = {};
			RVS.LIB.OBJ.sliderPackageAdditionsUID = {};
			RVS.LIB.OBJ.sliderPackageAdditionsInstalled = {};
			installNextTemplate(par);
		});
	}

	/*
	ENTER IN SLIDE
	*/
	function enterInModuleSlide(id,slideid) {

		RVS.LIB.OBJ.selectedSlideId = id;
		if (RVS.LIB.OBJ.items.moduleslides[RVS.LIB.OBJ.selectedSlideId].layers===undefined)
			RVS.F.ajaxRequest('get_layers_by_slide',{slide_id:slideid},function(response) {
				if (response.success) {
					var empty = true;
					if (response.layers!==undefined && response.layers!==null) for (var i in response.layers) if (response.layers.hasOwnProperty(i))  if (!empty) continue; else empty = i=="top" || i=="bottom" || i=="middle";
					if (empty)
						RVS.F.showInfo({content:RVS_LANG.nolayersinslide, type:"success", showdelay:0, hidedelay:2, hideon:"", event:"" });
					else {
						RVS.LIB.OBJ.items.moduleslides[RVS.LIB.OBJ.selectedSlideId].layers = RVS.F.safeExtend(true,{},response.layers);
						RVS.F.layerImportList();
					}
				}
			});
		else
			RVS.F.layerImportList();
	}
	/*
	IMPORT LAYERS FUNCTIONS
	*/

	function checkImportChildren(_) {
		//SELECT /DESELECT ALL CHILDRENS AND SUBLINGS IF NEEDED
		if ((_.dataset.type==="column" || _.dataset.type==="row" || _.dataset.type==="group")) {
			var lie = _.parentNode.getElementsByClassName('layimpli_element');
			if (_.className.indexOf('selected')>=0)
				for (let i in lie) {
					if(!lie.hasOwnProperty(i)) continue;
					if (lie[i].className!==undefined && lie[i].className.indexOf('selected')==-1) lie[i].className += " selected";
				}
			else
				for (let i in lie) {
					if(!lie.hasOwnProperty(i)) continue;
					if(lie[i].className) lie[i].className = lie[i].className.replace('selected','');
				}
		}

		// SELECT PARENT NODES IF NEEDED
		if (_.dataset.puid!=-1 && _.className.indexOf('selected')>=0) {
			var _IL = RVS.LIB.OBJ.items.moduleslides[RVS.LIB.OBJ.selectedSlideId].layers;
			jQuery('#layi_'+_.dataset.puid).addClass("selected");
			if (_IL[_.dataset.puid]!==undefined && _IL[_.dataset.puid].type==="column")
				jQuery('#layi_'+_IL[_.dataset.puid].group.puid).addClass("selected");
		}

		//SELECT UNSELECTED EMPTY COLUMNS IN SELECTED ROWS
		for (var i in RVS.LIB.OBJ.import.layers) if (RVS.LIB.OBJ.import.layers.hasOwnProperty(i)) {
			if (RVS.LIB.OBJ.import.layers[i].className!==undefined) {
				let ds = RVS.LIB.OBJ.import.layers[i].dataset;
				if (ds.type=="row" && RVS.LIB.OBJ.import.layers[i].className.indexOf("selected")>=0) {
					var lie = RVS.LIB.OBJ.import.layers[i].parentNode.getElementsByClassName('layimpli_element layimpli_level_1');
					for (let i in lie) {
						if(!lie.hasOwnProperty(i)) continue;
						if (lie[i].className!==undefined && lie[i].className.indexOf('selected')==-1) lie[i].className += " selected";
					}
				}
			}
		}

	}

	//Create Single Markup for 1 Import Element
	function importListSingleMarkup(_,level,i) {
		var _h='	<div id="layi_'+_.uid+'" class="layimpli_element layimpli_level_'+level+'" data-uid="'+_.uid+'" data-type="'+_.type+'" data-puid="'+_.group.puid+'">';
		_h +='		<i class="layimpli_icon material-icons">'+RVS.F.getLayerIcon(_.type)+'</i>';
		_h +='		<div class="layimpli_icon_title">'+_.alias+'</div>';
		_h +='		<div class="layimpli_icon_dimension">'+_.size.width.d.v+' x '+_.size.height.d.v+'</div>';
		if (_.actions.action.length>0) _h +='		<div class="layimpli_icon_dimension">'+RVS_LANG.layerwithaction+'</div>';
		var trigby = RVS.F.layerFrameTriggeredBy({layerid:_.uid, src:RVS.LIB.OBJ.items.moduleslides[RVS.LIB.OBJ.selectedSlideId].layers});
		if (trigby.alias!=="" && trigby.uid!=="")
			_h +='		<div class="layimpli_icon_dimension">'+RVS_LANG.triggeredby+' '+trigby.alias+'</div>';
		_h +='		<div class="layimpli_icon_checbox material-icons">radio_button_unchecked</div>';
		_h +='	</div>';
		return _h;
	}

	//Update list of To Do import Elements and draw selected/unselected States
	function updateCheckedLayerImportElements() {
		RVS.LIB.OBJ.import.toImport = [];
		for (var i in RVS.LIB.OBJ.import.layers) {
			if(!RVS.LIB.OBJ.import.layers.hasOwnProperty(i)) continue;
			let ds = RVS.LIB.OBJ.import.layers[i].dataset;
			if (RVS.LIB.OBJ.import.layers[i]!==undefined && RVS.LIB.OBJ.import.layers[i].className!==undefined) {
				if (RVS.LIB.OBJ.import.layers[i].className.indexOf('selected')>=0) {
					RVS.LIB.OBJ.import.toImport.push(ds.uid);
					RVS.LIB.OBJ.import.layers[i].getElementsByClassName('layimpli_icon_checbox')[0].innerHTML = "check_circle_outline";
				}
				else
					RVS.LIB.OBJ.import.layers[i].getElementsByClassName('layimpli_icon_checbox')[0].innerHTML = "radio_button_unchecked";
			}
		}

		jQuery('#layers_import_feedback').html((RVS.LIB.OBJ.import.toImport.length>0 ? RVS.LIB.OBJ.import.toImport.length+" "+RVS_LANG.nrlayersimporting : RVS_LANG.nothingselected));

	}



	/*
	BUILD A LIST WITH LAYERS TO SELECT, NAVIGATE
	*/
	RVS.F.buildLayerListToSelect = function(_) {
		//BUILD LIST OF LAYERS
		var markup = '<div class="layimpli_main_wrap">',
			cache = {root:""};

		// LAYERS
		for (var i in _){
			if(!_.hasOwnProperty(i)) continue;
			if (_[i].type!=="zone") {
				_[i] = RVS.F.safeExtend(true,RVS.F.addLayerObj(_[i].type,undefined,true),_[i]);
				if (_[i].group!==undefined && _[i].type!=="row" && _[i].type!=="group" && _[i].type!=="column") {
					if (_[i].group.puid==-1)
						cache.root += importListSingleMarkup(_[i],0,i);
					else {
						cache[_[i].group.puid] = cache[_[i].group.puid]== undefined ? "" : cache[_[i].group.puid];
						cache[_[i].group.puid] += importListSingleMarkup(_[i],(_[_[i].group.puid].type=="column" ? 2 : 1),i);
					}
				}
			}
		}

		// COLUMNS
		for (var i in _){
			if(!_.hasOwnProperty(i)) continue;
			if (_[i].type==="column") {
				cache[_[i].group.puid] = cache[_[i].group.puid]==undefined ? "" : cache[_[i].group.puid];
				cache[_[i].group.puid] += '<div class="layimpli_group_wrap">';
				cache[_[i].group.puid] += importListSingleMarkup(_[i],1,i);
				cache[_[i].group.puid] +='<div class="layimpli_group_inner">';
				if (cache[_[i].uid]!==undefined) cache[_[i].group.puid] += cache[_[i].uid];
				cache[_[i].group.puid] +='	</div>';
				cache[_[i].group.puid] +='</div>';
			}
		}

		// ROWS
		for (var i in _) {
			if(!_.hasOwnProperty(i)) continue;
			if (_[i].type==="row" || _[i].type==="group") {
				markup += '<div class="layimpli_group_wrap">';
				markup +=	importListSingleMarkup(_[i],0,i);
				markup +='	<div class="layimpli_group_inner">';
				if (cache[_[i].uid]!==undefined) markup += cache[_[i].uid];
				markup +='	</div>';
				markup +='</div>';
			}
		}
		markup += cache.root;
		markup += '</div>';

		return markup;
	};



	/*
	BUILD LAYER IMPORT LIBRARY
	*/
	RVS.F.layerImportList = function() {

		jQuery('#rb_modal_underlay').appendTo('body');

		// ADD LISTENERS
		if (RVS.LIB.OBJ.import===undefined || RVS.LIB.OBJ.import.basics===undefined) {
			jQuery('.rb-modal-wrapper[data-modal="rbm_layerimport"]').appendTo('body');
			RVS.LIB.OBJ.import = { container : jQuery('#rbm_layerimport_list'), basics:true};
			RVS.DOC.on('click','#rbm_layerimport .rbm_close',function() {
				jQuery('#rb_modal_underlay').appendTo('#slider_settings');
				RVS.F.RSDialog.close();
			});

			// Select / Deselect Layers
			RVS.DOC.on('click','.layimpli_element',function() {
				jQuery(this).toggleClass("selected");
				checkImportChildren(this);
				updateCheckedLayerImportElements();
			});

			// Import Layers
			RVS.DOC.on('click','#layers_import_from_slides_button',function() {
				RVS.F.showWaitAMinute({fadeIn:100,text:RVS_LANG.importinglayers});
				setTimeout(RVS.F.importSelectedLayers,200);
			});

		}
		RVS.LIB.OBJ.import.container[0].innerHTML = RVS.F.buildLayerListToSelect(RVS.LIB.OBJ.items.moduleslides[RVS.LIB.OBJ.selectedSlideId].layers);
		RVS.LIB.OBJ.import.container.RSScroll({ suppressScrollX:true});
		RVS.LIB.OBJ.import.layers = RVS.LIB.OBJ.import.container[0].getElementsByClassName('layimpli_element');
		//OPEN DIALOG
		RVS.F.RSDialog.create({modalid:'rbm_layerimport', bgopacity:0.85});

	};

	/*
	RESER FILTERS
	*/
	RVS.F.resetAllFilters = function() {
		RVS.LIB.OBJ.selectedPage = 1;
		jQuery('#sel_olibrary_sorting').val("datedesc").trigger('change.select2RS');
		RVS.DOC.trigger('updateObjectLibraryOverview',{val:"datedesc", eventparam:"#reset_objsorting",ignoreRebuild:true,ignoreCookie:true});
	};


/*******************************
 	INTERNAL FUNCTIONS
*******************************/

	RVS.F.closeObjectLibrary = function() {
		unselectOLItems();
		RVS.LIB.OBJ.moduleInFocus = false;
		tpGS.gsap.fromTo(RVS.LIB.OBJ.container_Library,0.7,{autoAlpha:1,display:"block",scale:1},{scale:0.8,autoAlpha:0,display:"none",ease:"power3.inOut"});
		tpGS.gsap.fromTo('#ol_header, #ol_footer',0.5,{autoAlpha:1},{autoAlpha:0,ease:"power3.inOut"});
		RVS.LIB.OBJ.open = false;
	};

	function getObjByUID(uid,libratytype) {
		var ret;
		for (var i in RVS.LIB.OBJ.items[libratytype]) {
			if(!RVS.LIB.OBJ.items[libratytype].hasOwnProperty(i)) continue;
			ret = RVS.LIB.OBJ.items[libratytype][i].uid === uid ? RVS.LIB.OBJ.items[libratytype][i] : ret;
		}
		return ret;
	}

	function getObjByID(id,libratytype) {
		var ret;
		for (var i in RVS.LIB.OBJ.items[libratytype]) {
			if(!RVS.LIB.OBJ.items[libratytype].hasOwnProperty(i)) continue;
			ret = RVS.LIB.OBJ.items[libratytype][i].id === id ? RVS.LIB.OBJ.items[libratytype][i] : ret;
		}
		return ret;
	}

	function getObjByHandle(handle,libratytype) {
		var ret;
		for (var i in RVS.LIB.OBJ.items[libratytype]) {
			if(!RVS.LIB.OBJ.items[libratytype].hasOwnProperty(i)) continue;
			ret = RVS.LIB.OBJ.items[libratytype][i].handle === handle ? RVS.LIB.OBJ.items[libratytype][i] : ret;
		}
		return ret;
	}

	function getModuleTemplateByUID(uid) {
		return getObjByUID(uid,"moduletemplates");
	}

	function setModuleTemplateInstalled(_,modal) {

		for (var i in RVS.LIB.OBJ.items.moduletemplates) {
			if(!RVS.LIB.OBJ.items.moduletemplates.hasOwnProperty(i)) continue;
			if (RVS.LIB.OBJ.items.moduletemplates[i].uid === _.uid) {
				RVS.LIB.OBJ.items.moduletemplates[i].installed = _.hiddensliderid;
				if (modal) RVS.LIB.OBJ.items.moduletemplates[i].modal = "1";
				if (RVS.LIB.OBJ.items.moduletemplates[i].ref!==undefined)
					RVS.LIB.OBJ.items.moduletemplates[i].ref.find('.installed_notinstalled').html(RVS_LANG.installed);
				//SET ALL CHILDREN TO INSTALLED
				if (_.children) {
					for (var ch in RVS.LIB.OBJ.items.moduletemplateslides) {
						if(!RVS.LIB.OBJ.items.moduletemplateslides.hasOwnProperty(ch)) continue;
						if (RVS.LIB.OBJ.items.moduletemplateslides[ch].parent == RVS.LIB.OBJ.items.moduletemplates[i].id) {
							RVS.LIB.OBJ.items.moduletemplateslides[ch].installed = _.hiddensliderid;
							RVS.LIB.OBJ.items.moduletemplateslides[ch].slideid = _.slideids[parseInt(RVS.LIB.OBJ.items.moduletemplateslides[ch].slideid,0)];
							if (RVS.LIB.OBJ.items.moduletemplateslides[ch].ref!==undefined) RVS.LIB.OBJ.items.moduletemplateslides[ch].ref.find('.installed_notinstalled').html(RVS_LANG.installed);
						}
					}
				}
			}
		}
	}

	/*
	function setModuleTemplateSlidesInstalled(uid,hiddensliderid) {
		for (var i in RVS.LIB.OBJ.items.moduletemplateslides) {
			if(!RVS.LIB.OBJ.items.moduletemplateslides.hasOwnProperty(i)) continue;
			if (RVS.LIB.OBJ.items.moduletemplateslides[i].uid === uid) {
				RVS.LIB.OBJ.items.moduletemplateslides[i].installed = hiddensliderid;
				if (RVS.LIB.OBJ.items.moduletemplateslides[i].ref!==undefined)
					RVS.LIB.OBJ.items.moduletemplateslides[i].ref.find('.installed_notinstalled').html(RVS_LANG.installed);
			}
		}
	}
	*/

	function installNextTemplate(_) {

		if (_.index<=_.amount) {

			var uid= _.uids[_.index],
				temp = getModuleTemplateByUID(uid);

			if (temp.modal===1 || temp.modal==="1") {
				RVS.LIB.OBJ.sliderPackageModal = true;
				RVS.LIB.OBJ.sliderPackageModalsOrig.push(""+temp.installed);
				RVS.LIB.OBJ.sliderPackageModalsOrigUid.push(temp.uid);
			}

			if (temp.additions!==undefined && temp.additions!=="") {
				RVS.LIB.OBJ.sliderPackageAdditionsUID[temp.uid] = temp.additions;
				RVS.LIB.OBJ.sliderPackageAdditionsInstalled[temp.installed] = temp.additions;
			}


			if (RVS.LIB.OBJ.reDownloadTemplate || temp.installed==false) {
				RVS.F.ajaxRequest('import_template_slider', {folderid:(sliderLibrary!==undefined ? sliderLibrary.selectedFolder : -1) ,uid:uid}, function(response){
						if (response.success) {
							response.silent = true;
							response.ignoreAjaxFolderMove = true;
							response.slider.modal = jQuery.inArray(""+response.hiddensliderid, RVS.LIB.OBJ.sliderPackageModalsOrig)>=0 || jQuery.inArray(response.uid, RVS.LIB.OBJ.sliderPackageModalsOrigUid)>=0;
							setModuleTemplateInstalled({uid:uid,hiddensliderid:response.hiddensliderid, modal:response.slider.modal});
							if (RVS.LIB.OBJ.success!==undefined && RVS.LIB.OBJ.success.slider!==undefined) RVS.DOC.trigger(RVS.LIB.OBJ.success.slider,response);
							RVS.LIB.OBJ.sliderPackageIds.push(response.slider.id);
							if (response.map!==undefined && response.map.slider!==undefined) RVS.LIB.OBJ.sliderPackageReferenceMap.slider_map = RVS.F.safeExtend(true,RVS.LIB.OBJ.sliderPackageReferenceMap.slider_map,response.map.slider);
							if (response.map!==undefined && response.map.slides!==undefined) RVS.LIB.OBJ.sliderPackageReferenceMap.slides_map = RVS.F.safeExtend(true,RVS.LIB.OBJ.sliderPackageReferenceMap.slides_map,response.map.slides);
							if (response.slider.modal) RVS.LIB.OBJ.sliderPackageModals.push(response.slider.id);
							if (RVS.LIB.OBJ.sliderPackageAdditionsUID[response.uid]!==undefined) RVS.LIB.OBJ.sliderPackageAdditions[response.slider.id] = RVS.LIB.OBJ.sliderPackageAdditionsUID[response.uid];
						}
						_.index++;
						installNextTemplate(_);
				},undefined,undefined,RVS_LANG.installpackage+'<br><span style="font-size:17px; line-height:25px;">'+_.name+' ('+(_.index+1)+' / '+(_.amount+1)+')</span>');
			} else {
				RVS.F.ajaxRequest('install_template_slider', {folderid:(sliderLibrary!==undefined ? sliderLibrary.selectedFolder : -1) ,uid:uid, sliderid:temp.installed}, function(response){
						if (response.success) {
							response.silent = true;
							response.ignoreAjaxFolderMove = true;
							if (RVS.LIB.OBJ.success!==undefined && RVS.LIB.OBJ.success.slider!==undefined) RVS.DOC.trigger(RVS.LIB.OBJ.success.slider,response);
							RVS.LIB.OBJ.sliderPackageIds.push(response.slider.id);
							if (response.map!==undefined && response.map.slider!==undefined) RVS.LIB.OBJ.sliderPackageReferenceMap.slider_map = RVS.F.safeExtend(true,RVS.LIB.OBJ.sliderPackageReferenceMap.slider_map,response.map.slider);
							if (response.map!==undefined && response.map.slides!==undefined) RVS.LIB.OBJ.sliderPackageReferenceMap.slides_map = RVS.F.safeExtend(true,RVS.LIB.OBJ.sliderPackageReferenceMap.slides_map,response.map.slides);
							if (jQuery.inArray(""+response.hiddensliderid, RVS.LIB.OBJ.sliderPackageModalsOrig)>=0 || jQuery.inArray(response.uid, RVS.LIB.OBJ.sliderPackageModalsOrigUid)>=0) RVS.LIB.OBJ.sliderPackageModals.push(response.slider.id);
							if (RVS.LIB.OBJ.sliderPackageAdditionsUID[response.uid]!==undefined) RVS.LIB.OBJ.sliderPackageAdditions[response.slider.id] = RVS.LIB.OBJ.sliderPackageAdditionsUID[response.uid];
							if (RVS.LIB.OBJ.sliderPackageAdditionsInstalled[response.hiddensliderid]!==undefined) RVS.LIB.OBJ.sliderPackageAdditions[response.slider.id] = RVS.LIB.OBJ.sliderPackageAdditionsInstalled[response.hiddensliderid];
						}
						_.index++;
						installNextTemplate(_);
				},undefined,undefined,RVS_LANG.installpackage+'<br><span style="font-size:17px; line-height:25px;">'+_.name+' ('+(_.index+1)+' / '+(_.amount+1)+')</span>');
			}
		} else {
			//Set Package Installed:
			setModuleTemplateInstalled({uid:_.folderuid,hiddensliderid:true});
			if (RVS.LIB.OBJ.createBlankPage && RVS.LIB.OBJ.success && RVS.LIB.OBJ.success.draftpage) RVS.DOC.trigger(RVS.LIB.OBJ.success.draftpage,{pages:RVS.LIB.OBJ.sliderPackageIds,modals:RVS.LIB.OBJ.sliderPackageModals,additions:RVS.LIB.OBJ.sliderPackageAdditions});
			RVS.F.closeObjectLibrary();
			//SAVE FOLDER STRUCTURE IF THERE IS ANY
			var folderid = (sliderLibrary!==undefined ? sliderLibrary.selectedFolder : -1);
			if (folderid!==-1) {
				folderid = RVS.F.getOVSliderIndex(folderid);
				RVS.F.ajaxRequest('save_slider_folder', {id:sliderLibrary.sliders[folderid].id, children:sliderLibrary.sliders[folderid].children}, function(response){});
				// Check if Parrents neet to be saved
				if (sliderLibrary.sliders[folderid].parent!==-1) {
					var parentfolderid = RVS.F.getOVSliderIndex(sliderLibrary.sliders[folderid].parent);
					RVS.F.ajaxRequest('save_slider_folder', {id:sliderLibrary.sliders[parentfolderid].id, children:sliderLibrary.sliders[parentfolderid].children}, function(response){});
				}
			}

			// IF MODAL EXISTS, WE NEED TO REMAP THE REFERENCES
			if (RVS.LIB.OBJ.sliderPackageModal) RVS.F.ajaxRequest('adjust_modal_ids', { map:RVS.LIB.OBJ.sliderPackageReferenceMap},function(response) {});

		}
	}

	function setFavorite(_) {
		for (var i in RVS.LIB.OBJ.items[_.type]) {
			if(!RVS.LIB.OBJ.items[_.type].hasOwnProperty(i)) continue;
			if (RVS.LIB.OBJ.items[_.type][i].id===_.id) RVS.LIB.OBJ.items[_.type][i].favorite = _.do==="add" ? true : false;
		}
	}

	function updateSearchPlaceHolder(force) {
		if (force) jQuery('#searchobjects').val("");
		var _ = jQuery('li.ol_filter_listelement.selected');
		if (_.length>0 && _!==undefined)
			jQuery('#searchobjects').attr('placeholder',RVS_LANG.search+" "+renameTag(_[0].dataset.title).t+" ...");

	}

	function unselectOLItems() {
		jQuery('.olibrary_item.selected').removeClass("selected");
		RVS.LIB.OBJ.container_Underlay.hide();
		jQuery('#obj_library_mediapreview').remove();
		RVS.LIB.OBJ.moduleInFocus = false;
	}

	// GET SLIDE INDEX
	RVS.F.getSliderIndex = function(id) {
		var ret = -1;
		//id = parseInt(id,0);
		for (var i in RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType]) {
			if(!RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType].hasOwnProperty(i)) continue;
			if (RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][i].id == id) ret = i;
		}
		return ret;
	};

	// GET INDEX OF ELEMENT BASED ON TYPE AND ID
	RVS.F.getModuleIndex = function(id,type) {
		var ret = -1;
		//id = parseInt(id,0);
		for (var i in RVS.LIB.OBJ.items[type]) {
			if(!RVS.LIB.OBJ.items[type].hasOwnProperty(i)) continue;
			if (RVS.LIB.OBJ.items[type][i].id == id) ret = i;
		}
		return ret;
	};


	function getMaxItemOnPage() {
		var hor = Math.floor((RVS.LIB.OBJ.container_OutputWrap.width()) / 287),
			ver = Math.floor((RVS.LIB.OBJ.container_OutputWrap.innerHeight())/235);
		if (hor===0 || ver===0) {
			hor = Math.floor((window.innerWidth - 330) / 287);
			ver = Math.floor((window.innerHeight - 160)/235);
		}

		return hor*ver;
	}


	// CHECK IF THE MODULETEMPLATES PARRENT PACKAGE INSTALLABLE OR NOT
	function isPackageInstallable(_) {
		var paritem,
			uids = [],
			installable = true;

		for (var i in RVS.LIB.OBJ.items.moduletemplates) {
			if(!RVS.LIB.OBJ.items.moduletemplates.hasOwnProperty(i)) continue;
			if (RVS.LIB.OBJ.items.moduletemplates[i].package_id === _.packageId) {
				if  (RVS.LIB.OBJ.items.moduletemplates[i].package_parent==="true")
					paritem = RVS.LIB.OBJ.items.moduletemplates[i];
				else
					uids.push({o:parseInt(RVS.LIB.OBJ.items.moduletemplates[i].package_order,0), uid:RVS.LIB.OBJ.items.moduletemplates[i].uid});
			}
		}

		uids.sort(function(a,b) {return a.o - b.o});
		var retuids = [];
		for (var i in uids) if (uids.hasOwnProperty(i)) if (uids[i]!==undefined && uids[i].uid!==undefined) retuids.push(uids[i].uid);

		if (paritem!==undefined) for (var pi in paritem.plugin_require) if (paritem.plugin_require.hasOwnProperty(pi)) if (paritem.plugin_require[pi].installed!="true") installable=false;

		return {installable:installable, uids:retuids};
	}


	// SMART PAGINATION
	function smartPagination() {
		RVS.LIB.OBJ.pageAmount = parseInt(RVS.LIB.OBJ.pageAmount,0);
		RVS.LIB.OBJ.selectedPage = parseInt(RVS.LIB.OBJ.selectedPage,0);
		/* var middle = Math.floor((RVS.LIB.OBJ.pageAmount - RVS.LIB.OBJ.selectedPage)/2); */
		jQuery('.page_button.ol_pagination').each(function() {
			var i = parseInt(this.dataset.page,0),
				s = false;
			if ((i===1) || (i===RVS.LIB.OBJ.pageAmount)) s = true;
			if (RVS.LIB.OBJ.selectedPage<4 && i>0 && i<5) s = true;
			if (RVS.LIB.OBJ.selectedPage>RVS.LIB.OBJ.pageAmount-3 && i>RVS.LIB.OBJ.pageAmount-4 && i<9999) s = true;
			if (i<9999 && i>=RVS.LIB.OBJ.selectedPage-1 && i<=RVS.LIB.OBJ.selectedPage+1 && i>0) s = true;
			if ((RVS.LIB.OBJ.selectedPage>=4 && i===-9999) || (RVS.LIB.OBJ.selectedPage<= RVS.LIB.OBJ.pageAmount-3 && i===9999)) s = true;
			if (RVS.LIB.OBJ.pageAmount<8) if (i==9999 || i==-9999) s=false; else s=true;
			this.style.display = s ? "inline-block" : "none";
		});
	}


	// DELIVER PARRENT FOLDERS OF ELEMENT
	function getParentPath(pd) {
		var f = [];
		f.push(pd);
		var quit = 0;
		while (pd !== -1 && quit!==20) {
			var sindex = RVS.F.getSliderIndex(pd);
			pd = sindex!==-1 && RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][pd]!==undefined ? RVS.LIB.OBJ.items[RVS.LIB.OBJ.selectedType][pd].parent || -1 : -1;
			f.push(pd);
			quit++;
		}
		return f;
	}

	// SELECTED FILTER MATCH
	function filterMatch(_) {
		return ((_.filter === _.o.source || _.filter === _.o.type || _.filter === _.o.size || jQuery.inArray(_.filter,_.o.tags)>=0));
	}


	//UPDATE SCROLLBARS
	function updateScollbarFilters() {
		RVS.LIB.OBJ.container_Filters.RSScroll({
			wheelPropagation:false
		});
		RVS.LIB.OBJ.container_OutputWrap.RSScroll({
			wheelPropagation:false
		});
	}

	function renameTag(a) {
		switch(a) {
			case "Slider": return {o:1, t:"Slider"};break;
			case "Carousel": return {o:2, t:"Carousel"};break;
			case "Hero": return {o:3, t:"Hero"};break;
			case "Website": return {o:4, t:"Website"};break;
			case "Premium": return {o:5, t:"Special FX"};break;
			case "Postbased": return {o:6, t:"Post Based"};break;
			case "Socialmedia": return {o:7, t:"Social Media"};break;
			case "Revolution Base": return { o:8, t:"Basic"};break;
			default:
			return {o:0, t:a};
			break;
		}
	}

	function setSpecialTagOrder(a) {
		switch(a) {

		}
	}

	function sortTagsAsNeeded() {
		$(".listitems.autosort").each(function(){
	    $(this).html($(this).children('li').sort(function(a, b){
	        return ($(b).data('position')) < ($(a).data('position')) ? 1 : -1;
	    }));
	});
	}


	// ADD THE SINGLE FILTERS
	function addObjectFilter(_) {
		var subtags = _.tags!==undefined && Object.keys(_.tags).length>0;
			_html = '<div data-subtags="'+subtags+'" data-type="'+_.groupType+'" id="ol_filter_'+_.groupType+'" data-title="'+_.groupAlias+'" class="ol_filter_type '+(_.groupopen ? "open" : "")+'"><div data-filter="all" data-type="'+_.groupType+'" data-title="'+_.groupAlias+'" data-subtags="'+subtags+'" class="ol_filter_listelement ol_filter_headerelement"><i class="material-icons">'+_.icon+'</i><span class="filter_type_name">'+_.groupAlias+'</span></div>';
		if (subtags) {
			_html +='<ul class="ol_filter_group">';
			_html +='<li data-type="'+_.groupType+'" data-filter="all" data-title="All '+_.groupAlias+'" class="ol_filter_listelement"><span class="filter_tag_name">All</span></li>';
			var prel = new Array(),
				dynl = new Array();
			for (var i in _.tags) {
				if(!_.tags.hasOwnProperty(i)) continue;
				var m = _.groupType==="moduletemplates" ? renameTag(_.tags[i]) : {o:0, t:_.tags[i]};
				//if (_.groupType==="moduletemplates") {
				if (m.o==0)
					dynl.push('<li data-type="'+_.groupType+'" data-filter="'+i+'" data-title="'+RVS.F.capitalise(i)+'" class="ol_filter_listelement"><span class="filter_tag_name">'+m.t+'</span></li>');
				else
					prel[m.o] = '<li data-type="'+_.groupType+'" data-filter="'+i+'" data-title="'+RVS.F.capitalise(i)+'" class="ol_filter_listelement"><span class="filter_tag_name">'+m.t+'</span></li>';
			}
			for (var i in prel) if (prel.hasOwnProperty(i)) if (prel[i]!==undefined) _html += prel[i];
			for (var i in dynl) if (dynl.hasOwnProperty(i)) if (dynl[i]!==undefined) _html += dynl[i];
			_html += '</ul>';
		}
		_html += '</div>';

		RVS.LIB.OBJ.container_Filters.append(_html);


	}

})();