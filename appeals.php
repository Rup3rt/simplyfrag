	<?php
include('../scripts/functions/datedifference.php');
include('../scripts/functions/tosteamid.php');
include('../scripts/functions/banlistformat.php');
include('../scripts/functions/banappeals.php');

if (!((isset($_SESSION["steamid"]) && $_GET['id'] == $_SESSION["steamid"]) || (isset($auth) && $auth['staff'] !== '0'))){
	die('You have no permission!');
}

$bans = banListFormat(toSteamID($_GET['id']));
$appeals = banAppeals(toSteamID($_GET['id']));
$servers_result = DB::query("SELECT * FROM ".MYSQL_PREFIX_PORTAL."servers ");

	$servers = array();
	foreach ($servers_result as $item ){
		$servers[$item['id']] = $item['name'];
	}

	$appeals_sent = array();
	foreach ($appeals as $item ){
		$appeals_sent[] = $item['id'];
	}
$profileuserdata = DB::queryFirstRow("SELECT * FROM " . MYSQL_PREFIX_PORTAL . "users WHERE steamid=%s", $_GET["id"]);
if (empty($profileuserdata)) {
	echo  translate('action.not_found');
	exit();
}

$user_name = translate('action.not_auth');
if (isset($_SESSION["steamid"])) {
	$userdata = DB::queryFirstRow("SELECT * FROM " . MYSQL_PREFIX_PORTAL . "users WHERE steamid=%s", $_SESSION["steamid"]);
	$user_name = $userdata['name'];
}

	$ban_id = $_GET['ban_id'];
	$result    = DB::query("SELECT * FROM " . MYSQL_PREFIX_PORTAL . "appealreplies WHERE appealid=%s ORDER BY replydate DESC", $ban_id);
	$appeal = DB::queryFirstRow("SELECT status  FROM " . MYSQL_PREFIX_PORTAL . "appeals	WHERE banid = %s",  $ban_id);
	$statuses = array(
		"active"      =>  translate('appeal.status.active'),
		"in_progress" => translate('appeal.status.in_progress'),
		'approve'     => translate('appeal.status.approve'),
		'reject'      => translate('appeal.status.reject')
	);
	
	
?>

<!-- The list of bans for you -->

	<?php

	$isset_bans = false;
	foreach ($bans as $index => $ban) {
		if (in_array($ban['id'], $appeals_sent)) continue;
		if (!($ban['lengthunix'] > time() || $ban['lengthunix'] == 0)) continue;
		$isset_bans = true;
		break;
	}
	?>

	<?php if($isset_bans){?>
<div class="row">
    <h2><?php echo translate('user.appeal.title')?></h2>
    <table class="main_table">
        <thead>
        <tr>
			<th><?php echo translate('date')?></th>
			<th><?php echo translate('time_left')?></th>
			<th><?php echo translate('expiry')?></th>
			<th><?php echo translate('admin')?></th>
			<th><?php echo translate('reason')?></th>
			<th><?php echo translate('servers')?></th>
			<th></th>
        </tr>
        </thead>
		<tbody> <?php
		foreach ($bans as $index => $ban) {
			if (in_array($ban['id'], $appeals_sent)) continue;
			if (!($ban['lengthunix'] > time() || $ban['lengthunix'] == 0)) continue;
			?>

        
        <tr data-toggle="collapse" data-target="#appeal-<?php echo  $ban['id'] ?>">
          <td><?php echo  $ban["time"]?></td>
          <td><?php echo  $ban["length"]?></td>
          <td><?php echo  $ban["expiration"]?></td>
          <td><?php echo  $ban["admin"]?></td>
          <td><?php echo  $ban["reason"]?></td>
          <td><?php echo  (!empty($servers[$ban["serverid"]]))?$servers[$ban["serverid"]]:$ban["serverid"]?></td>
          <td>
		  <button class="caret-span glyphicon glyphicon-triangle-bottom"></button>
		  </td>
        </tr>
		<tr class="sub_table collapse" id="appeal-<?php echo  $ban['id'] ?>">
			<td colspan="12">
                <div class="chat_form">
                <div class="chat_header">
                <h4 style="text-align: left;"><?php echo translate('admin.appeals.banappeal')?> - <?php echo  $ban['id'] ?> </h4><p class="pull-right"><?php echo translate('appeal.chat.title')?><span> <?php echo $statuses[$appeal['status']]?></span></p>
                    </div>
                <div class="chat_body clearfix">
				
				<?php foreach ($result as $reply) { ?>
				<?php $replycheck = DB::query("SELECT * FROM ".MYSQL_PREFIX_PORTAL."users WHERE steamid=%s",toCommunityID($reply['replyid']));
				$replystaffcheck = DB::query("SELECT * FROM ".MYSQL_PREFIX_PORTAL."userlevels WHERE id=%s",$replycheck['0']['auth']); ?>
				<?php if ($replystaffcheck['0']['staff'] == '0') { ?>
                    <div class="chat_admin pull-left">
                        <div class="login_img_wraper pull-left"><img class="img-responsive img-circle login_img" src="<?php echo  $replycheck['0']["avatar"] ?>"></div>
                        <div class="chat_box"><?php echo  $reply['reply'] ?>
                            <div class="user_name"><?php echo  $reply['replyname'] ?></div>
                            <div class="time_chat"><?php echo translate('appeal.chat.time_at')?> <?php echo  date("d.m.Y H:i:s", $reply['replydate']) ?></div>
                        </div>
					</div> 
				<?php } else {  ?>
					<div class="chat_you pull-right">
                        <div class="chat_box"><?php echo  $reply['reply'] ?>
                            <div class="user_name"><?php echo  $reply['replyname'] ?></div>
                            <div class="time_chat"><?php echo translate('appeal.chat.time_at')?> <?php echo  date("d.m.Y H:i:s", $reply['replydate']) ?></div>
                        </div>
                        <div class="login_img_wraper pull-right"><img class="img-responsive img-circle login_img" src="<?php echo  $replycheck['0']["avatar"] ?>"></div>
					</div> 
				<?php } } ?>

                </div>
				<br >
                <div class="chat_footer">
                    <textarea type="text" class="input_formessage" placeholder="Type your message..."></textarea>
                    <button class="send_message"><img src="<?php echo URL; ?>/img/send_ico.png"></button>
                </div>
				</div>
			</td> <!-- /.td colspan="8" -->
        </tr> <!-- /#demo -->
        <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>
<script type="text/javascript">

	var action_url = '<?php echo URL?>/u/action.php?id=<?php echo $_GET["id"]?>';


	$(function(){
		$('.form-appeal').click(function(){
			var id = $(this).data('id');
			$.get(action_url + '&action=form&ban_id='+id,function(data){
				$('#modal-form .modal-content').html(data);
				$('#modal-form').modal();
			})
			return false;
		})

		$('.view-appeal').click(function(){
			var id = $(this).data('id');
			$.get(action_url + '&action=show-chat&ban_id='+id,function(data){
				$('#modal-form .modal-content').html(data);
				$('#modal-form').modal();
			})
			return false;
		})
	})



	$(document).on('submit', '#send-appeal', function () {
		$.post(action_url + '&action=add-appeal', $(this).serialize(), function (data) {
			if (data.success == 'true') {
				alert('<?php echo translate('user.appeal.appeal_sent')?>');
			}
			$('#modal-form').modal('toggle');
			window.location.reload();
		}, 'json')
		return false;
	})

	$(document).on('submit', '#send-reply', function() {
		$.post(action_url + '&action=add-reply', $(this).serialize(), function (data) {
			if (data.success == 'true') {
				alert('<?php echo translate('user.appeal.reply_sent')?>');
			}
			$('#modal-form').modal('toggle');
			window.location.reload();
		}, 'json')
		return false;
	})





</script>
	<?php if(!empty($appeals)){?>

	<!-- Appeal dialogues -->
<div class="row">
                <h2><?php echo translate('user.appeal.history.title')?></h2>
     <table class="main_table">
        <thead>
        <tr>
			<th><?php echo translate('date')?></th>
			<th><?php echo translate('expiry')?></th>
			<th><?php echo translate('admin')?></th>
			<th><?php echo translate('reason')?></th>
			<th><?php echo translate('server')?></th>
          	<th><?php echo translate('user.appeal.history.appeal')?></th>
			<th><?php echo translate('status')?></th>
			<th></th>
        </tr>
        </thead>
		<tbody> <?php foreach ($appeals as $appeal) { ?>

        <tr data-toggle="collapse" data-target="#appeal-<?php echo  $appeal['id'] ?>">
          <td><?php echo  $appeal["time"]?></td>
          <td><?php echo  $appeal["expiration"]?></td>
          <td><?php echo  $appeal["admin"]?></td>
          <td><?php echo  $appeal["admin"]?></td>
         <td><?php echo  (!empty($servers[$appeal["serverid"]]))?$servers[$appeal["serverid"]]:$appeal["serverid"]?></td>
          <td><?php echo  $appeal["appealdate"]?></td>
          <td><?php echo  $appeal["status"]?></td>
          <td><button class="caret-span glyphicon glyphicon-triangle-bottom"></button>
        </tr>
		<tr class="sub_table collapse" id="appeal-<?php echo  $appeal['id'] ?>">
			<td colspan="12">
                <div class="chat_form">
                <div class="chat_header">
                <h4 style="text-align: left;"><?php echo translate('admin.appeals.banappeal')?> - <?php echo  $appeal['id'] ?> </h4><p class="pull-right"><?php echo translate('appeal.chat.title')?><span> <?php echo $statuses[$appeal['status']]?></span></p>
                    </div>
                <div class="chat_body clearfix">
				
				<?php foreach ($result as $reply) { ?>
				<?php $replycheck2 = DB::query("SELECT * FROM ".MYSQL_PREFIX_PORTAL."users WHERE steamid=%s",toCommunityID($reply['replyid']));
				$replystaffcheck2 = DB::query("SELECT * FROM ".MYSQL_PREFIX_PORTAL."userlevels WHERE id=%s",$replycheck2['0']['auth']); ?>
				<?php if ($replystaffcheck2['0']['staff'] == '0') { ?>
                    <div class="chat_admin pull-left">
                        <div class="login_img_wraper pull-left"><img class="img-responsive img-circle login_img" src="<?php echo  $replycheck2['0']["avatar"] ?>"></div>
                        <div class="chat_box"><?php echo  $reply['reply'] ?>
                            <div class="user_name"><?php echo  $reply['replyname'] ?></div>
                            <div class="time_chat"><?php echo translate('appeal.chat.time_at')?> <?php echo  date("d.m.Y H:i:s", $reply['replydate']) ?></div>
                        </div>
					</div> 
				<?php } else {  ?>
					<div class="chat_you pull-right">
                        <div class="chat_box"><?php echo  $reply['reply'] ?>
                            <div class="user_name"><?php echo  $reply['replyname'] ?></div>
                            <div class="time_chat"><?php echo translate('appeal.chat.time_at')?> <?php echo  date("d.m.Y H:i:s", $reply['replydate']) ?></div>
                        </div>
                        <div class="login_img_wraper pull-right"><img class="img-responsive img-circle login_img" src="<?php echo  $replycheck2['0']["avatar"] ?>"></div>
					</div> 
				<?php } } ?>

                </div>
				<br >
                <div class="chat_footer">
                    <textarea type="text" class="input_formessage" placeholder="Type your message..."></textarea>
                    <button class="send_message"><img src="<?php echo URL; ?>/img/send_ico.png"></button>
                </div>
				</div>
			</td> <!-- /.td colspan="8" -->
        </tr> <!-- /#demo -->
        <?php } ?>
        </tbody>
    </table>
</div>
	<?php }?>

	<div class="modal fade" id="modal-form" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">

			</div>
		</div>
	</div>