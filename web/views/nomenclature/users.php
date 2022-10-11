<?php
    use models\User;
    use soffit\Crypt;

    $u = true; // нужен для nom_list.php
    try {
        $editUser = User::permission('nomUsers_edit');
    } catch (\Exception $e) {}
?>
<div class="row">
    <p class="lead text-info text-center">Списки Наименований</p>

    <div class="col-xs-12 stats_table">
        <?php require_once __DIR__ . "/nom_list.php" ?>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade" id="tab6">
                <br/>
                    <div class="panel panel-default">
                        <div class="panel-heading text-bold text-center">
                            <?php if ( $editUser ): ?>
                                <a class="btn btn-sm btn-primary pull-right" id="userAddModal" type="button" title="Изменить" data-id="" data-toggle="modal" data-target="#userEditModal" role="button">
                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                    Добавить нового пользователя
                                </a>
                                <div class="clearfix"></div>
                        <?php endif; ?>
                        </div>
                        <table class="table table-hover cursorArrow">
                            <thead>
                            <tr class="thead11 bg-info-light">
                                <th width="5%" class="text-center">№</th>
                                <th width="20%" class="text-center">ФИО</th>
                                <th width="20%" class="text-center">Роль</th>
                                <th width="50%" class="text-center">Участки</th>
                                <th width="5%" class="text-center"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $n=1; foreach ( $users??[] as $user ): ?>
                                <tr class="collsRow">
                                    <td><?=$n++?></td>
                                    <td><?= trueIsset($user['fullFio']) ? $user['fullFio'] : $user['fio'] ?></td>
                                    <td>
                                        <span title="<?=$user['role']['description']?>" data-toggle="tooltip"><?=$user['role']['name']?></span>
                                    </td>
                                    <td>
                                        <?php foreach ( $user['locNames']??[] as $areaName => $subNames ): ?>
                                            <span><?=$areaName?>: </span><i><?=rtrim($subNames,', ') . ". "?></i>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ( $editUser ): ?>
                                            <?php if ( (int)$user['access'] !== 1 ): ?>
                                                <a class="btn btn-sm btn-default" type="button" title="Изменить" data-id="<?=Crypt::strEncode($user['id']);?>" data-toggle="modal" data-target="#userEditModal" role="button">
                                                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                                </a>
                                            <?php elseif ( User::getAccess() === 1 ): ?>
                                                <a class="btn btn-sm btn-default" type="button" title="Изменить" data-id="<?=Crypt::strEncode($user['id']);?>" data-toggle="modal" data-target="#userEditModal" role="button">
                                                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
            </div>
        </div>
    </div>
</div>