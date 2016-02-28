

<div class="list-group" id="list">
    <div class="list-group-item">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalForm" data-action="add">новый комментарий</button>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">Комментарий</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="message-text" class="control-label">Содержимое:</label>
                        <textarea class="form-control" name="content" id="message-text"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" name="send">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" crossorigin="anonymous"></script>

<script>

    /**
     * Загрузка комментариев верхнего уровня
     */
    $(function () {
        $.ajax({
            type: 'GET',
            url: '/commentary',
            dataType: 'json',
            success: function (data) {
                if (data.success && data.items.length > 0) {
                    $.each(data.items, function (i, item) {
                        addCommentary(item.id, null, item.content);
                    });
                }
            }
        });
    });


    /**
     * Модальное окно (добавление/редактирование)
     */
    $('#modalForm').on('show.bs.modal', function (event) {
        var modal = $(this);
        var button = $(event.relatedTarget);

        var elContent = button.closest('div').find('p');
        modal.find('[name=content]').val(button.data('action') == 'edit' ? elContent.text() : '');

        var btnSend = modal.find('[name=send]');
        btnSend.unbind('click');
        btnSend.click(function () {

            // добавление комментария
            if (button.data('action') == 'add') {
                var pid = elContent ? elContent.data('id') : '';
                $.ajax({
                    type: 'POST',
                    url: '/commentary',
                    data: {
                        pid: elContent ? elContent.data('id') : '',
                        content: modal.find('[name=content]').val()
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            addCommentary(data.item.id, pid, data.item.content);
                        }
                    }
                });
            }

            // редактирование комментария
            if (button.data('action') == 'edit') {
                var id = elContent.data('id');
                $.ajax({
                    type: 'PUT',
                    url: '/commentary/' + id,
                    data: {
                        content: modal.find('[name=content]').val()
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            editCommentary(id, data.item.content);
                        }
                    }
                });
            }

            modal.modal('hide');
        });
    });


    /**
     * Отображение вложенных
     */
    function show(id) {
        $.ajax({
            type: 'GET',
            url: '/commentary?pid=' + id,
            dataType: 'json',
            success: function (data) {
                if (data.success && data.items.length > 0) {
                    $.each(data.items, function (i, item) {
                        addCommentary(item.id, item.pid, item.content);
                    });
                }
            }
        });
    }


    /**
     * Добавление комментария на страницу
     */
    function addCommentary(id, pid, content) {
        var elList = pid ? $('#list_' + pid) : $('#list');
        elList.append('<div class="list-group-item">' +
            '<p class="list-group-item-text" style="margin-bottom: 10px" data-id="' + id + '">' + content + '</p>' +
            '<button type="button" class="btn btn-primary" onclick="show(' + id + ')">раскрыть</button> ' +
            '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalForm" data-action="add">добавить</button> ' +
            '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalForm" data-action="edit">редактировать</button> ' +
            '<button type="button" class="btn btn-primary" onclick="delCommentary(' + id + ')">удалить</button> ' +
            '<div class="list-group" id="list_' + id + '"></div>' +
            '</div>');
    }


    /**
     * Изменение комментария
     */
    function editCommentary(id, content) {
        $('p[data-id=' + id + ']').text(content);
    }


    /**
     * Удаление комментария
     */
    function delCommentary(id) {
        $.ajax({
            type: 'DELETE',
            url: '/commentary/' + id,
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $('#list_' + id).closest('div.list-group-item').remove();
                }
            }
        });
    }


</script>
