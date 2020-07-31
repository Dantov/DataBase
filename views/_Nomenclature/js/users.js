"use strict";

function UsersEditing()
{
    this.modal = document.querySelector('#userEditModal');
    this.submitUserButton = this.modal.querySelector('.submitUserData');

    this.editUser_ID = null;
    this.addUser = null;
    this.deletedPermlist = [];
    this.operationStatus = false;
}
UsersEditing.prototype.init = function()
{
    let that = this;
    let userEditModal = $('#userEditModal');

    userEditModal.on('show.bs.modal', function (e) {

        that.editUser_ID = e.relatedTarget.getAttribute('data-id');

        if ( that.editUser_ID )
        {
            that.showUserEditModal();
        } else {
            that.userAddModal();
        }
    });
    userEditModal.on('hide.bs.modal', function (e) {
        let wcListUL = document.querySelector('.wcList');
        let userMTProd = document.querySelector('#userMTProd');
        let userPermList = document.querySelector('.userPermList');
        let addedPermListUL = document.querySelector('.addedPermissionsList');

        if ( wcListUL )
            wcListUL.innerHTML = "";
        if ( userMTProd )
            userMTProd.innerHTML = "";
        if ( userPermList )
            userPermList.innerHTML = "";
        if ( addedPermListUL )
            addedPermListUL.innerHTML = "";

        this.deletedPermlist = [];
        this.editUser_ID = null;
        this.addUser = null;
        this.operationStatus = false;
    });
    // Обработчик на панель в Юзер модале
    let permissionsPanel = document.getElementById('permissionsPanel');
    if ( permissionsPanel )
    {
        permissionsPanel.addEventListener('click',function (e) {
            $(this.nextElementSibling).collapse('toggle');
            let panel = this.parentElement;
            panel.classList.toggle('panel-success');
            panel.classList.toggle('panel-primary');
        });
    }


    $("#alertResponse").iziModal({
        timeout: 5000,
        zindex: 1100,
        width: '700px',
        timeoutProgressbar: true,
        pauseOnHover: true,
        restoreDefaultContent: true,
    });
    $(document).on('closing', '#alertResponse', function (e) {
        if ( that.operationStatus === false )
        {
            that.submitUserButton.classList.remove('disabled');
        }
    });
    $(document).on('closed', '#alertResponse', function (e) {
        if ( that.operationStatus === true )
            document.location.reload(true);
    });


    this.addWCList();
    this.addPermission();
    this.buttonSubmitUserInit();
    this.buttonDellUserInit();

    debug('UserEdit init ok!');
};




UsersEditing.prototype.addWCList = function()
{
    let that = this;
    document.querySelector('.addWCList').addEventListener('click',function (event) {
        if ( !event.target.classList.contains('addWC') ) return;

        let a = event.target;
        let wcID = a.getAttribute('data-wcID');
        let wcName = a.innerHTML;

        let newWcLi = document.querySelector('.wcListProto').cloneNode(true);
            newWcLi.children[0].innerHTML = wcName;
            newWcLi.children[1].value = wcID;
            newWcLi.classList.remove('wcListProto','hidden');

        let addedWCLi = document.querySelector('.wcList').appendChild(newWcLi);
            addedWCLi.querySelector('.deleteWCListItem').addEventListener('click', function () {
                that.deleteWCListItem(this);
            });
    });
};
UsersEditing.prototype.deleteWCListItem = function( buttonDell )
{
    buttonDell.parentElement.remove();
};


UsersEditing.prototype.addPermission = function()
{
    let that = this;
    let addPermList = this.modal.querySelector('.addPermList');
    if ( addPermList )
    {
        addPermList.addEventListener('click',function (event) {
            if ( !event.target.classList.contains('addPermission') ) return;

            let a = event.target;
            let pID = a.getAttribute('data-permID');
            let pName = a.innerHTML;

            let newPLi = document.querySelector('.wcListProto').cloneNode(true);
                newPLi.children[0].innerHTML = pName;
                newPLi.children[1].name = "addPermList[]";
                newPLi.children[1].value = pID;
                newPLi.children[2].setAttribute('class','close remAddedPerm');
                newPLi.classList.remove('wcListProto','hidden');

            let addedPermission = document.querySelector('.addedPermissionsList').appendChild(newPLi);
                addedPermission.querySelector('.remAddedPerm').addEventListener('click', function () {
                    that.deletePermListItem(this);
                });
        });
    }
};
UsersEditing.prototype.deletePermListItem = function( buttonDell )
{
    if ( buttonDell.classList.contains('remCurrentPerm') )
        this.deletedPermlist.push(buttonDell.getAttribute('data-cpID'));

    buttonDell.parentElement.remove();
};




UsersEditing.prototype.showUserEditModal = function(e)
{
    let submitUserData = this.modal.querySelector('.submitUserData');
        submitUserData.classList.remove('disabled');
        submitUserData.children[1].innerHTML = 'Изменить';

    this.modal.querySelector('#userEditModalLabel').innerHTML='Редактировать данные пользователя';

    if ( this.modal.querySelector('.deleteUser') )
    {
        this.modal.querySelector('.deleteUser').classList.remove('hidden');
    }

    let rowID = this.editUser_ID;
    let that = this;

    $.ajax({
        url: "/nomenclature/editUser",
        type: 'POST',
        data: {
            userShow: 1,
            showUserID: rowID,
        },
        dataType:"json",
        success:function(data) {
            debug(data);

            if ( data.debug )
            {
                debugModal( data.debug );
                return;
            }

            if ( data.error )
                that.resultModalCall( 'error', data.error.message, data.error.code, data.error );

            that.editUser_ID = data.id;
            let fio = [];
            if ( data.fullFio )
            {
                fio = data.fullFio.split(' ');
            } else {
                fio[0] = data.fio;
            }

            that.modal.querySelector('#userName').innerHTML = data.fio;
            that.modal.querySelector('#userFirstName').setAttribute('value', fio[0]);
            that.modal.querySelector('#userFirstName').value = fio[0];

            that.modal.querySelector('#userSecondName').setAttribute('value', fio[1] ? fio[1] : '');
            that.modal.querySelector('#userSecondName').value = fio[1] ? fio[1] : '';

            that.modal.querySelector('#userThirdName').setAttribute('value', fio[2] ? fio[2] : '');
            that.modal.querySelector('#userThirdName').value = fio[2] ? fio[2] : '';

            let userLog = that.modal.querySelector('#userLog');
                userLog.setAttribute('value', data.login);
                userLog.value = data.login;
            let userPass = that.modal.querySelector('#userPass');
                userPass.setAttribute('value', data.pass);
                userPass.value = data.pass;


            if ( data.location )
            {
                let wcListUL = document.querySelector('.wcList');
                let locations = data.location.split(',');
                $.each(workingCentersDB, function(name, wcList)
                {
                    $.each(wcList, function(i, wc)
                    {
                        if ( locations.includes(i) )
                        {
                            let newWcLi = document.querySelector('.wcListProto').cloneNode(true);
                                newWcLi.children[0].innerHTML = wc.name + ": " + wc.descr;
                                newWcLi.children[1].value = i;
                                newWcLi.classList.remove('wcListProto','hidden');
                                newWcLi.classList.add('bg-info');
                            let addedWCLi = wcListUL.appendChild(newWcLi);
                                addedWCLi.querySelector('.deleteWCListItem').addEventListener('click', function () {
                                    that.deleteWCListItem(this);
                            });
                        }
                    });
                });
            }

            if ( data.presets )
            {
                let userMTProd = document.querySelector('#userMTProd'); // select
                let selected = false;
                $.each(data.presets, function (name, preset) {
                    selected = false;

                    if (+preset.id === +data.access)
                        selected = true;
                    //debug(+name,'nnn');

                    if ( !isInteger(+name) || selected )
                    {
                        let option = document.createElement('option');
                        option.setAttribute('value', name);
                        option.setAttribute('title', preset.description);
                        option.innerHTML = preset.name;
                        if (selected)
                            option.selected = true;

                        userMTProd.appendChild(option);
                    }
                });
            }

            if ( data.permissions )
            {
                let permListUL = document.querySelector('.userPermList');
                let pCount = 0;
                $.each(data.permissions, function(iter, perm)
                {
                    let newPermLi = document.querySelector('.wcListProto').cloneNode(true);
                        newPermLi.children[0].innerHTML = perm.description;
                        newPermLi.children[1].remove();
                        newPermLi.children[1].setAttribute('data-cpID', perm.id);
                        newPermLi.children[1].setAttribute('class','close remCurrentPerm');
                        newPermLi.classList.remove('wcListProto','hidden');

                    let addPermLi = permListUL.appendChild(newPermLi);
                        addPermLi.querySelector('.remCurrentPerm').addEventListener('click', function () {
                            that.deletePermListItem(this);
                        });
                    pCount++;
                });
                $('#permissionsPanel .count').html("(" + pCount + ")") ;
            }
        },
        error:function (error) {
            that.resultModalCall( 'serverError', error.responseText, error.status );
        }
    }); //AJAX
};

/**
 * Накинем обработчик на кнопку Отправить данные Юзера
 */
UsersEditing.prototype.buttonSubmitUserInit = function()
{
    let that = this;
    this.submitUserButton.addEventListener('click', function() {

        let button = this;

        let editUser_form = new FormData( document.getElementById('editUser_form') );
        editUser_form.append('editUser_ID', that.editUser_ID);
        if ( that.addUser )
        {
            editUser_form.append('userAddEdit', '1');
        } else {
            editUser_form.append('userAddEdit', '2');

            if ( that.deletedPermlist )
            {
                $.each(that.deletedPermlist, function (i, permID) {
                    editUser_form.append('deletedPermlist[]',permID);
                });
            }
        }

        $.ajax({
            url: "/nomenclature/editUser",
            type: 'POST',
            data: editUser_form,
            processData: false,
            contentType: false,
            beforeSend: function() {
                button.classList.add('disabled');
            },
            success:function(data) {
                data = JSON.parse(data);
                debug(data);
                if ( data.debug )
                {
                    debugModal( data.debug );
                    return;
                }

                if ( data.success )
                {
                    $('#userEditModal').modal('hide');
                    that.resultModalCall( 'success', data.success.message, data.success.code );
                } else if ( data.error ) {
                    that.resultModalCall( 'error', data.error.message, data.error.code, data.error );
                }
            },
            error:function (error) {
                that.resultModalCall( 'serverError', error.responseText, error.status );
            }
        });
    });
};



UsersEditing.prototype.userAddModal = function()
{
    this.editUser_ID = 0;
    this.addUser = true;

    this.modal.querySelector('#userEditModalLabel').innerHTML='Добавить Нового Пользователя';
    this.modal.querySelector('#userName').innerHTML = "ФИО";
    this.modal.querySelector('#userFirstName').value = "";
    this.modal.querySelector('#userSecondName').value = '';
    this.modal.querySelector('#userThirdName').value = '';
    this.modal.querySelector('#userLog').value = "";
    this.modal.querySelector('#userPass').value = "";
    this.modal.querySelector('.wcList').innerHTML = '';

    if ( this.modal.querySelector('.userPermList') )
        this.modal.querySelector('.userPermList').innerHTML = '';

    if ( this.modal.querySelector('.addedPermissionsList') )
        this.modal.querySelector('.addedPermissionsList').innerHTML = '';

    if ( this.modal.querySelector('.deleteUser') )
        this.modal.querySelector('.deleteUser').classList.add('hidden');

    this.modal.querySelector('.submitUserData').children[1].innerHTML = 'Добавить';
    let that = this;
    $.ajax({
        url: "/nomenclature/addUser_presets",
        type: 'POST',
        data: {
            getPresets: 1,
        },
        dataType:"json",
        success:function(data) {
            debug(data);
            if ( data.presets )
            {
                let userMTProd = that.modal.querySelector('#userMTProd'); // select
                $.each(data.presets, function (name, preset) {
                    let option = document.createElement('option');
                        option.setAttribute('value', name);
                        option.setAttribute('title', preset.description);
                        option.innerHTML = preset.name;
                    if (+preset.id === 0)
                        option.selected = true;
                    userMTProd.appendChild(option);
                });
            } else if ( data.error ) {
                that.resultModalCall( 'error', data.error.message, data.error.code, data.error );
            }

        },
        error: function (error) {
            that.resultModalCall( 'serverError', error.responseText, error.status );
        }
    });
};


/**
 * Накинем обработчик на кнопку удаления Юзера
 */
UsersEditing.prototype.buttonDellUserInit = function()
{
    let dellButton = this.modal.querySelector('.deleteUser');
    if ( dellButton )
    {
        let that = this;
        dellButton.addEventListener('click', function() {
            let button = this;
            if ( confirm('Удалить Пользователя безвозвратно?') )
            {
                $.ajax({
                    url: "/nomenclature/editUser",
                    type: 'POST',
                    data: {
                        userDell: 1,
                        userID: that.editUser_ID,
                        userMTProd: that.modal.querySelector('#userMTProd').value,
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        button.classList.add('disabled');
                        that.modal.querySelector('.submitUserData').classList.add('disabled');
                    },
                    success:function(data) {
                        debug(data);
                        if ( data.success )
                        {
                            $('#userEditModal').modal('hide');
                            that.resultModalCall( 'success', data.success.message, data.success.code );

                        } else if ( data.error ) {
                            that.resultModalCall( 'error', data.error.message, data.error.code, data.error );
                        }
                    },
                    error: function (error) {
                        that.resultModalCall( 'serverError', error.responseText, error.status );
                    }
                });
            }

        });
    }
};

UsersEditing.prototype.resultModalCall = function( callType, message, code, respObject )
{

    let alertResponse = $('#alertResponse');
    alertResponse.iziModal('setWidth', '600');
    switch (callType)
    {
        case "success":
        {
            this.operationStatus = true;
            alertResponse.iziModal('setHeaderColor', '#d09d16');
            alertResponse.iziModal('setIcon', 'far fa-check-circle');
            alertResponse.iziModal('setTitle', message);
            alertResponse.iziModal('setSubtitle', 'Операция прошла успешно!');
        } break;
        case "error":
        {
            alertResponse.iziModal('setHeaderColor', 'rgb(189, 91, 91)');
            alertResponse.iziModal('setIcon', 'fas fa-exclamation-triangle');
            alertResponse.iziModal('setTitle', 'Операция завершена с ошибкой!' + " Код: " +  code );
            alertResponse.iziModal('setSubtitle', message );
            if ( respObject.file )
            {
                alertResponse.iziModal('setWidth', "90%");
                alertResponse.iziModal('resetProgress');
                let text = '<div class="p1">';
                if ( respObject.file )
                    text += '<p class="textSizeMiddle bg-warning p1">File: <b>'+ respObject.file +' __ on line: '+ respObject.line +'</b></p>';

                if ( respObject.previous )
                    text += '<p>Previous: <b>'+ respObject.previous +'</b></p>';
                
                if ( respObject.trace )
                {
                    text += '<p class="bg-info p1"> <b>Trace:</b> <br>';
                    $.each(respObject.trace, function (i, tarceObj) {
                        text += '<p class="mb-1 brb-2-secondary">File: <b>'+ tarceObj.file +' __ on line: '+ tarceObj.line +'</b><br>';
                        text += 'Class: <b>'+ tarceObj.class +' '+ tarceObj.type +'<i>'+  tarceObj.function + '()</i></b></p>';
                    });
                    text += '</p>';
                }
                alertResponse.iziModal('setContent', text);
            }
        } break;
        case "serverError":
        {
            alertResponse.iziModal('setHeaderColor', 'rgb(189, 91, 91)');
            alertResponse.iziModal('setIcon', 'fas fa-bug');
            alertResponse.iziModal('setTitle', 'Ошибка на сервере! Попробуйте позже.');
            alertResponse.iziModal('setSubtitle', "Код: " + code);
            alertResponse.iziModal('setWidth', "100%");
            alertResponse.iziModal('pauseProgress');

            alertResponse.iziModal('setContent', '<div>'+ message +'</div>');
        } break;
        default:
        {
        } break;
    }
    alertResponse.iziModal("open");
};

let usersEditing = new UsersEditing();
usersEditing.init();

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
});