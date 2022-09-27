function HandlerFiles( dropArea, button, filterTypes )
{
    this.dropArea = dropArea;
    this.button = button;

    this.fileTypes = Array.isArray(filterTypes) ? filterTypes : [];
    this.fileBuffer = []; //здесь хранятся все загруженные файлы. это массив файлов, не FileList

    this.imageFilesBuffer = [];
    this.stlFilesBuffer = [];
    this.rhinoFilesBuffer = [];
    this.aiFilesBuffer = [];

    this.stlOveralSize = 0;
    this.rhinoOveralSize = 0;

    this.init();
}

HandlerFiles.prototype.init = function() {

    let self = this;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        self.dropArea.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        self.dropArea.addEventListener(eventName, function () {
            self.highlight(this);
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        self.dropArea.addEventListener(eventName, function () {
            self.unhighlight(this);
        }, false);
    });

    this.dropArea.addEventListener('drop', function(e) {
        let dt = e.dataTransfer;
        let files = dt.files; // FileList
        self.handleFiles(files);
    }, false);

    this.button.addEventListener('click', function() {

        let typesStr = self.fileTypes.join(',');
        let fileInput = document.createElement('input');

        fileInput.setAttribute('type','file');
        fileInput.setAttribute('multiple','');
        fileInput.setAttribute('accept',typesStr);

        fileInput.addEventListener('change',function () {
            self.handleFiles(this.files);
        });

        fileInput.click();
    }, false);

    let removeDataFiles = document.querySelectorAll('.removeDataFiles');
    $.each(removeDataFiles, function (i, button) {
        button.addEventListener('click',function () {
            let dataType = this.getAttribute('data-type');
            self.removeDataFiles(dataType);
        },false);
    });

    debug('HandlerFiles Init(ok)');
};

HandlerFiles.prototype.highlight = function(dropArea)
{
    dropArea.classList.add('highlight');
};

HandlerFiles.prototype.unhighlight = function(dropArea)
{
    dropArea.classList.remove('highlight');
};



HandlerFiles.prototype.handleFiles = function(files)
{
    let self = this;
    Array.prototype.push.apply( this.fileBuffer, files );
    files = [...files];
    //debug( self.fileTypes,'fileTypes');
    files.forEach(function (file)
    {
        let arr_split = file.name.split('.');
        let fileExtension = arr_split[arr_split.length-1].toLowerCase();
        if ( self.fileTypes.includes(file.type) )
        {   // картинки здесь
            self.imageFilesBuffer.push(file);
            self.previewFile(file);
            debug(self.imageFilesBuffer,'imageFilesBuffer');
        } else if ( self.fileTypes.includes('.' + fileExtension) ) {
            // Для остальных типов
            self.addDataFile(file, fileExtension);
        }
    });
    this.fileBuffer = [];
    //debug(this.fileBuffer,'fileBuffer');
    // удаляем файлы из Буффера, не соотвсетст. заданным типам
    /*
    for ( let i = 0; i < this.fileBuffer.length; i++ )
    {
        if ( !this.fileTypes.includes(this.fileBuffer[i].type) )
        {
            this.fileBuffer.splice(i, 1);
            --i;
        }
    }
    */
};


/**
 * Data Files
 */
HandlerFiles.prototype.addDataFile = function(file,type)
{
    let areaID = '';
    let removeBtnID = '';
    let size = 0;
    let mb = 'Мб';
    let overallSize = '';

    switch ( type.toLowerCase() )
    {
        case "stl":
            stl_mgx_case(this,file);
            break;
        case "mgx":
            stl_mgx_case(this,file);
            break;
        case "3dm":
            areaID = '3dm-files-area';
            removeBtnID = 'remove3dm';
            size = ( (file.size / 1024) / 1024 ).toFixed(2);
            this.rhinoOveralSize += file.size;

            this.rhinoFilesBuffer.push(file);
            debug(this.rhinoFilesBuffer,'rhinoFilesBuffer');

            overallSize = document.getElementById(removeBtnID).previousElementSibling.children[0];
            overallSize.innerHTML = ' ( ' + ((this.rhinoOveralSize / 1024) / 1024 ).toFixed(2) + ' Мб)';
            break;
        case "ai":
            ai_dxf_case(this,file);
            break;
        case "dxf":
            ai_dxf_case(this,file);
            break;
    }

    function stl_mgx_case(hf,file)
    {
        areaID = 'stl-files-area';
        removeBtnID = 'removeStl';
        size = ( (file.size / 1024) / 1024 ).toFixed(2);
        hf.stlOveralSize += file.size;

        hf.stlFilesBuffer.push(file);
        debug(hf.stlFilesBuffer,'stlFilesBuffer');

        overallSize = document.getElementById(removeBtnID).previousElementSibling.children[0];
        overallSize.innerHTML = '( ' + ((hf.stlOveralSize / 1024) / 1024 ).toFixed(2) + ' Мб)';
    }
    function ai_dxf_case(hf,file)
    {
        areaID = 'ai-files-area';
        removeBtnID = 'removeAi';
        size = ( (file.size / 1024) ).toFixed(2);
        mb = 'Кб';

        hf.aiFilesBuffer.push(file);
        debug(hf.aiFilesBuffer,'aiFilesBuffer');
    }


    let typeArea = document.getElementById(areaID);
    let removeBtn = document.getElementById(removeBtnID);

    let fileBlock = document.querySelector('.file-block-proto').cloneNode(true);
        fileBlock.classList.remove('file-block-proto', 'hidden');
        fileBlock.classList.add('file-block');
        fileBlock.children[0].src = '../../web/picts/icon_' + type + '.png';
        fileBlock.children[1].innerHTML = file.name + ' (' + size + ' ' + mb +')';


    //let size = ( (file.size / 1024) / 1024 ).toFixed(2);

    // let span = document.createElement('span');
    //     //span.classList.add('data-files-block','mr-1');
    //     span.classList.add('stlFileBlock','mr-1');
    //     span.innerHTML = file.name + ' (' + size + ' ' + mb +')';
    typeArea.appendChild(fileBlock);
    removeBtn.classList.remove('hidden');
};
HandlerFiles.prototype.removeDataFiles = function(type)
{
    let areaID = '';
    let removeBtnID = '';

    switch ( type.toLowerCase() )
    {
        case "stl":
            areaID = 'stl-files-area';
            removeBtnID = 'removeStl';
            this.stlFilesBuffer = [];
            this.stlOveralSize = 0;
            debug(this.stlFilesBuffer,'stlFilesBuffer');
            break;
        case "3dm":
            areaID = '3dm-files-area';
            removeBtnID = 'remove3dm';
            this.rhinoFilesBuffer = [];
            this.rhinoOveralSize = 0;
            debug(this.rhinoFilesBuffer,'rhinoFilesBuffer');
            break;
        case "ai":
            areaID = 'ai-files-area';
            removeBtnID = 'removeAi';
            this.aiFilesBuffer = [];
            debug(this.aiFilesBuffer,'aiFilesBuffer');
            break;
    }

    let overallSize = document.getElementById(removeBtnID).previousElementSibling.children[0];
    overallSize.innerHTML = '';

    let typeArea = document.getElementById(areaID);
    let removeBtn = document.getElementById(removeBtnID);
    if ( typeArea ) typeArea.innerHTML = '';
    removeBtn.classList.add('hidden');
};
/**
 * Превьюшка для картинок
 */
HandlerFiles.prototype.previewFile = function(file) {

    let self = this;

    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = function() {

        let imgRow = document.getElementById('proto_image_row').cloneNode(true);
            imgRow.removeAttribute('id');
            imgRow.setAttribute('fileId',file.lastModified);
            imgRow.classList.add('image_row');
            imgRow.classList.remove('hidden');

        let dellButton = imgRow.querySelector('.img_dell').children[0];
        dellButton.removeAttribute('onclick');
        dellButton.addEventListener('click',function () {
            self.removeImg(this);
        });
        imgRow.querySelector('select').addEventListener('change',function () {
            self.onSelect(this);
        });

        // в прототипе строки, в HTML, нет аттрибутов name
        // чтобы прототип строки не отправлялся вместе с формой, добавим его в JS
        let img_inputs = imgRow.querySelector('.img_inputs');
        img_inputs.children[0].setAttribute('name','image[id][]');
        img_inputs.children[0].setAttribute('value','');
        img_inputs.children[1].setAttribute('name','image[imgFor][]');

        let img = imgRow.getElementsByTagName('img');
            img[0].src = reader.result;

        //document.getElementById('picts').insertBefore(imgRow, self.dropArea.parentElement);
        document.getElementById('picts').appendChild(imgRow);
    }
};

HandlerFiles.prototype.onSelect = function(self)
{
    let options = self.options;
    let optText;
    for ( let opt in options )
    {
        if ( options[opt].selected && options[opt].value === 27 ) return;
        if ( options[opt].selected ) optText = options[opt].text;
    }

    let selects = $("select[name*='image[imgFor][]']");
    for ( let key in selects )
    {
        let select = selects[key];
        if ( select === self ) continue;

        let options = select.options;

        // Поик 'Нет' в текущем селекте
        let no;
        for ( let opt in options )
        {
            if (  +options[opt].value === 27 )
            {
                no = options[opt];
                break;
            }
        }

        // Ищем совпадения по статусам картинок. Может быть только 1 уникальный
        for ( let opt in options )
        {
            if ( options[opt].selected && options[opt].text === optText )
            {
                options[opt].selected = false;
                no.selected = true;
                //debug(options[opt].text);
                break;
            }
        }
    }

    //debug(self.options);
    //debug(selects);
};

HandlerFiles.prototype.removeImg = function(self)
{

    let toDell = self.parentElement.parentElement.parentElement.parentElement;
    for ( let i = 0; i < this.imageFilesBuffer.length; i++ )
    {
        if ( this.imageFilesBuffer[i].lastModified === +toDell.getAttribute('fileId') )
        {
            toDell.remove();
            this.imageFilesBuffer.splice(i, 1);
            break;
        }
    }
    debug(this.imageFilesBuffer);
};
HandlerFiles.prototype.removeScetchImg = function(self)
{
    let toDell = self.parentElement.parentElement.parentElement.parentElement;
    toDell.remove();
};
HandlerFiles.prototype.getImageFiles = function()
{
    return this.imageFilesBuffer;
};
HandlerFiles.prototype.getStlFiles = function()
{
    return this.stlFilesBuffer;
};
HandlerFiles.prototype.get3dmFiles = function()
{
    return this.rhinoFilesBuffer;
};
HandlerFiles.prototype.getAiFiles = function()
{
    return this.aiFilesBuffer;
};
