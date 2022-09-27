const ALERT_DEBUG = $('#alertDebug'); // ID дебаг окна
ALERT_DEBUG.iziModal({
    title: 'Debug',
    subtitle: '',
    headerColor: '#88A0B9',
    icon: 'fas fa-laptop-code', // fas fa-bug
    iconText: '',
    width: '80%',
    fullscreen: true,
    zindex: 1500,
    restoreDefaultContent: true,
});
function debugModal(message)
{
    ALERT_DEBUG.iziModal('setContent', '<div>'+ message +'</div>');
    ALERT_DEBUG.iziModal("open");
}