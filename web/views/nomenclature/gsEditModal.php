<div class="modal fade" id="gsEditModal" tabindex="-1" role="dialog" aria-labelledby="gsEditModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="gsEditModalLabel">Редактировать систему оценок</h4>
            </div>
            <div class="modal-body">
                <div class="panel panel-default">
                    <!-- Default panel contents -->
                    <form method="post" id="editGS_form">
                        <div class="panel-heading text-bold text-center"></div>
                        <!-- Table -->
                        <table class="table cursorArrow">
                            <thead>
                            <tr class="thead11">
                                <th width="" class="text-center p1">Примеры</th>
                                <th width="" class="text-center p1" title="Процент от базовой стоимости">%</th>
                                <th width="" class="text-center p1">Баллы</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr class="collsRow">
                                    <td class="p1" title="Вносить, через запятую, ID моделей из базы"><input type="text" name="examples" class="p-1 form-control input-sm editGS_examples" value=""></td>
                                    <td class="p1 text-center"><input type="number" name="basePercent" class="p-1 input-sm form-control editGS_basePercent" value=""></td>
                                    <td class="p1 text-center" title="Баллы">
                                        <input type="number" class="p-1 input-sm form-control editGS_PointsInput hidden" value="">
                                        <span class="editGS_Points"></span>
                                    </td>
                                </tr>
                                <tr class="collsRow">
                                    <td class="p1" colspan="3">
                                        <textarea  required rows="6" name="description" class="form-control editGS_description" style="width: 100%; resize: none;"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span> Отмена</button>
                <button type="button" class="btn btn-primary editGS_edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Изменить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="gsOKModal" tabindex="-1" role="dialog" aria-labelledby="gsOKModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="gsOKModalLabel">Редактировать систему оценок</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal"><span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span>ОК</button>
            </div>
        </div>
    </div>
</div>