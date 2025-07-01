


const CSSGUIService = {
    show: () => {
        mw.top().app.guiEditorBox.show();
    },
    hide: () => {
        mw.top().app.guiEditorBox.hide();
    },
    toggle: () => {
        mw.top().app.guiEditorBox.toggle();
    },
    isVisible: () => {
        return mw.top().app.guiEditorBox.visible()
    }
}

addEventListener('load', function(){
    addEventListener('keydown', function(e){
        if(e.key === "Escape") {
            CSSGUIService.hide()
        }
    });

    mw.app.canvas.on('canvasDocumentKeydown',function(e){

        if(e.key === "Escape") {
            CSSGUIService.hide()
        }

    });
})


export default CSSGUIService;
