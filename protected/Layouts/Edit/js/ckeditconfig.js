CKEDITOR.editorConfig = function( config ) {
    config.disableNativeSpellChecker = false;
    config.removePlugins = 'liststyle,tabletools,scayt,contextmenu';
    config.toolbar='MyConf';
    config.toolbar_MyConf = [
        { name: 'document', items : [ 'Undo','Redo'] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline', ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },

    ];
    extraPlugins = 'сохранить' ;

};