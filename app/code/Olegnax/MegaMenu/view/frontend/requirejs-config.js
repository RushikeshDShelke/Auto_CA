var config = {
	map: {
		'*': {
			'OxMegaMenu': 'Olegnax_MegaMenu/js/megamenu',
			'plugins/velocity': 'Olegnax_MegaMenu/js/velocity.min',
			'plugins/scrollbar': 'Olegnax_MegaMenu/js/perfect-scrollbar.jquery.min',
		}
	}
};
if(OX_MOBILE){
    delete config.map['*']['plugins/velocity'];
    delete config.map['*']['plugins/scrollbar'];
}