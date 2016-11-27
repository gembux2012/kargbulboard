CKEDITOR.editorConfig = function( config ) {
    config.toolbar_Full = [
        { name: 'document', items : [ 'Undo','Redo'] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Subscript','Superscript','Format' ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
        { name: 'tools', items : [ 'Maximize','Source'] }
    ];
};