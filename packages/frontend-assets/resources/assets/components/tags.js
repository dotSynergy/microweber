mw.tags = function(options){

    "use strict";

    options.element = mw.$(options.element)[0];
    options.size = options.size || 'xs'; // Changed from 'sm' to 'xs' for smaller tags

    this.options = options;
    this.options.map = this.options.map || {
        title: 'title',
        value: 'id',
        image: 'image',
        icon: 'icon'
    };
    this.map = this.options.map;
    var scope = this;
    /*
        data: [
            {title:'Some tag', icon:'<i class="icon"></i>'},
            {title:'Some tag', icon:'icon', image:'http://some-image/jpg.png'},
            {title:'Some tag', color:'warn'},
        ]
    */

    var _e = {};

    this.on = function (e, f) { _e[e] ? _e[e].push(f) : (_e[e] = [f]) };

    this.dispatch = function (e, f) { _e[e] ? _e[e].forEach(function (c){ c.call(this, f); }) : ''; };

    this.refresh = function(){
        mw.$(scope.options.element).empty();
        this.rend();
    };

    this.setData = function(data){
        this.options.data = data;
        this.refresh();
    };
    this.rend = function(){
        scope.options.element.classList.add('mw-tags--container', 'd-flex', 'flex-wrap', 'gap-2') // Reduced gap from 3 to 2

         $.each(this.options.data, function(i){
            var data = $.extend({index:i}, this);
            scope.options.element.appendChild(scope.tag(data));
        });
        if(this.options.inputField) {
            scope.options.element.appendChild(this.addInputField());
        }
    };

    this.addInputField = function () {
        this._field = document.createElement('input');
        this._field.className = 'mw-ui-invisible-field mw-ui-field-' + this.options.size;

        this._field.onkeydown = function (e) {
            var val = scope._field.value.trim();
            if(mw.event.is.enter(e) || mw.event.is.comma(e)) {
                e.preventDefault();

                if(val) {
                    scope.addTag({
                        title: val
                    });
                }
            } else if (mw.event.is.backSpace(e)) {
                if(!val) {
                    var last = scope.options.data[scope.options.data.length - 1];
                    scope.removeTag(scope.options.data.length - 1);
                    scope._field.value = scope.dataTitle(last) + ' ';
                    scope._field.focus();

                }
            }
            scope.handleAutocomplete(val, e)


        };
        return this._field;
    };
    this.handleAutocomplete = function (val, e) {
        if(this.options.autocomplete){



        }
    };



    this.dataValue = function(data){
        if(typeof data === 'string'){
            return data;
        }
        else{
            return data[this.map.value]
        }
    };

    this.dataImage = function(data){
        if(data[this.map.image]){
            var img = document.createElement('span');
            img.className = 'mw-ui-btn-img';
            img.style.backgroundImage = 'url('+data.image+')';
            return img;
        }
    };

    this.dataTitle = function(data){
        if(typeof data === 'string'){
            return data;
        }
        else{
            return data[this.map.title];
        }
    };

    this.dataIcon = function(data){
        if(typeof data === 'string'){
            return;
        }
        else{
            return data[this.map.icon];
        }
    };

     this.createImage = function (config) {
         var img = this.dataImage(config);
        if(img){
            return img;
        }
     };

     this.createIcon = function (config) {
        var ic = this.dataIcon(config);

        if(!ic && config.type){
            ic = mw.iconResolver(config.type)
        }
        var icon;
        if(typeof ic === 'string' && ic.indexOf('<') === -1){
            icon = document.createElement('i');

        }
        else{
            icon = ic;
        }
        icon = mw.element(icon).get(0);


        return icon;
     };

     this.removeTag = function (index) {
        var item = this.options.data[index];
        this.options.data.splice(index,1);
        this.refresh();
        mw.$(scope).trigger('tagRemoved', [item, this.options.data]);
        mw.$(scope).trigger('change', [item, this.options.data]);
     };






     this.unique = function () {
        var first = this.options.data[0];
        if(!first) return;
        var id = this.options.map.value;
        if(!first[id]) {
            id = this.options.map.title;
        }
        var i = 0, curr = first;
        var _findIndex = function (tag) {
            var tagId = isNaN(tag[id]) ? tag[id].toLowerCase() : tag[id];
            var currId = isNaN(curr[id]) ? curr[id].toLowerCase() : curr[id];
            return tagId == currId;
        };
        while (curr) {
            if (this.options.data.findIndex(_findIndex) === i) {
                i++;
            } else {
                this.options.data.splice(i, 1);
            }
            curr = this.options.data[i];
        }
     };

    this.addTag = function(data, index){
        index = typeof index === 'number' ? index : this.options.data.length;
        this.options.data.splice( index, 0, data );
        this.unique();
        this.refresh();
        if (this._field) {
            this._field.focus();
        }

        mw.$(scope).trigger('tagAdded', [data, this.options.data]);
        mw.$(scope).trigger('change', [data, this.options.data]);
    };

     this.tag = function (options) {
            var config = {
                close:true,
                tagBtnClass:'btn btn-' + this.options.size + ' mw-tag-animated' // Added animation class
            };

            $.extend(config, options);

         config.tagBtnClass +=  '  btn';

         if (this.options.outline){
             config.tagBtnClass +=  '-outline';
         }

         if (this.options.color){
             config.tagBtnClass +=  '-' + this.options.color;
         } else {
             config.tagBtnClass +=  '-primary'; // Default color for better appearance
         }

         if(this.options.rounded){
             config.tagBtnClass +=  ' btn-rounded';
         } else {
             config.tagBtnClass +=  ' rounded-pill'; // Default rounded style for modern look
         }

         // Add shadow and hover effects
         config.tagBtnClass += ' shadow-sm mw-tag-hover-effect';

            var tag_holder = document.createElement('span');
            var tag_button = document.createElement('span');

            tag_holder._index = config.index;
            tag_holder._config = config;
            tag_holder.dataset.index = config.index;

            tag_holder.className = 'mw-tag-wrapper'; // Single tag wrapper

             if(options.image){

             }

            // Create the main tag content with close button inside
            var tagContent = this.dataTitle(config);
            var closeIcon = '';

            if(config.close){
                closeIcon = `<span class="mw-tag-close-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="10" height="10" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M18 6l-12 12"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </span>`;
            }

            tag_button.className = config.tagBtnClass + ' mw-tag-single';
            tag_button.innerHTML = `<span class="mw-tag-content">${tagContent}</span>${closeIcon}`;

             if(typeof this.options.disableItem === 'function') {
                 if(this.options.disableItem(config)){
                     tag_holder.className += ' disabled';
                 }
             }
             if(typeof this.options.hideItem === 'function') {
                 if(this.options.hideItem(config)){
                     tag_holder.className += ' hidden';
                 }
             }

            var icon = this.createIcon(config);
            var image = this.createImage(config);

             if(image){
                 tag_button.querySelector('.mw-tag-content').prepend(image);
             }
             if(icon){
                 tag_button.querySelector('.mw-tag-content').prepend(icon);
             }

            tag_button.onclick = function (e) {
                var closeButton = e.target.closest('.mw-tag-close-inner');
                if(closeButton && config.close){
                    scope.removeTag(config.index);
                } else {
                    mw.$(scope).trigger('tagClick', [config, config.index, tag_holder]);
                    scope.dispatch('tagClick', [config, config.index, tag_holder]);
                }
            };

            tag_holder.appendChild(tag_button);
            return tag_holder;
        };

     this.init = function () {
         this.rend();

         // Add CSS styles for animations and effects
         if (!document.getElementById('mw-tags-styles')) {
             var style = document.createElement('style');
             style.id = 'mw-tags-styles';
             style.textContent = `
                .mw-tag-wrapper {
                    transition: all 0.2s ease-in-out;
                    transform: scale(1);
                    display: inline-block;
                }

                .mw-tag-wrapper:hover {
                    transform: scale(1.05);
                    z-index: 10;
                }

                .mw-tag-single {
                    display: inline-flex !important;
                    align-items: center !important;
                    gap: 0.25rem !important;
                    position: relative !important;
                    cursor: pointer !important;
                    min-height: 22px !important;
                    height: 22px !important;
                }

                .mw-tag-content {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.2rem;
                    height: 100%;
                }

                .mw-tag-close-inner {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 16px !important;
                    height: 16px !important;
                    border-radius: 50% !important;
                    background-color: rgba(255,255,255,0.2) !important;
                    transition: all 0.15s ease-in-out !important;
                    margin-left: 0.15rem !important;
                    opacity: 0.7 !important;
                    cursor: pointer !important;
                    flex-shrink: 0 !important;
                }

                .mw-tag-close-inner:hover {
                    background-color: rgba(255,255,255,0.3) !important;
                    opacity: 1 !important;
                    transform: scale(1.1) !important;
                }

                .mw-tag-close-inner svg {
                    width: 8px !important;
                    height: 8px !important;
                    stroke-width: 3 !important;
                }

                .mw-tag-animated {
                    transition: all 0.15s ease-in-out;
                    font-size: 0.55rem !important;
                    padding: 0.15rem 0.3rem !important;
                    line-height: 1.1 !important;
                    min-height: 22px !important;
                    height: 22px !important;
                    box-sizing: border-box !important;
                }

                .mw-tag-hover-effect:hover {
                    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.2) !important;
                    transform: translateY(-1px);
                    filter: brightness(1.1) saturate(1.2);
                }

                .mw-tags--container {
                    gap: 0.25rem !important;
                    align-items: center !important;
                }

                .btn-xs {
                    font-size: 0.55rem;
                    padding: 0.15rem 0.3rem;
                    line-height: 1.1;
                    min-height: 22px;
                    height: 22px;
                    box-sizing: border-box;
                }
             `;
             document.head.appendChild(style);
         }

         $(this.options.element).on('click', function (e) {
             if(e.target === scope.options.element){
                 $('input', this).focus();
             }
         })
     };
    this.init();
};

mw.treeTags = mw.treeChips = function(options){
    this.options = options;
    this.options.on = this.options.on || {};
    var scope = this;

    var tagsHolder = options.tagsHolder || mw.$('<div class="mw-tree-tag-tags-holder"></div>');
    var treeHolder = options.treeHolder || mw.$('<div class="mw-tree-tag-tree-holder"></div>');

    var treeSettings = $.extend({}, this.options, {element:treeHolder});


    const treeSelectedData = (selectedData) => {
        return (this.options.selectedData || selectedData || []).map(obj => {
            let curr =  scope.tree.get(obj);
            if(!curr) {
                curr = {};
                console.warn('Object can not be found in tree data', obj);
            } else {
                curr = curr._data || {}
            }
            return Object.assign({}, obj, curr);
        });
    }


    this.tree = new mw.tree(treeSettings);


    var tagsSettings = $.extend({}, this.options, {element:tagsHolder, data: treeSelectedData(this.options.selectedData || [])});

    this.tags = new mw.tags(tagsSettings);

    mw.$( this.options.element ).append(tagsHolder);
    mw.$( this.options.element ).append(treeHolder);

     mw.$(this.tags).on('tagClick', function(e, data){
         var li = scope.tree.get(data);

         if(li) {
             scope.tree.show(data);
         }

         li.scrollIntoView({behavior: "smooth", block: "center", inline: "center"});
     });
     mw.$(this.tags).on('tagRemoved', function(event, item){
         scope.tree.unselect(item);
     });
     mw.$(this.tree).on('selectionChange', function(event, selectedData){
        scope.tags.setData(selectedData);
        if (scope.options.on.selectionChange) {
            scope.options.on.selectionChange(selectedData)
        }
    });

};
