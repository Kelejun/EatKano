<!DOCTYPE html>
<html>

<head>
  <title>来看神仙 | 炸实验 - 排行榜</title>
  <meta item="description" content="来看神仙" />
  <meta charset="utf-8" />
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
  <?php
  @require 'conn.php';
  //最大显示页数
  $max_pages = 9;
  $lbtype = isset($_GET['lbtype']) ? $_GET['lbtype'] : 'day';
  //每页显示数量
  $num = 100;
  $CurrentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
  $CurrentUser = $_GET['name'];
  $offset = ($CurrentPage - 1) * $num;
  if ($lbtype == 'day') {
    $title = "日";
    $cond = "to_days(time) = to_days(now())";
  }
  if ($lbtype == 'week') {
    $title = "月";
    $cond = "DATE_SUB(CURDATE(), INTERVAL 31 DAY) <= date(time)";
  }
  if ($lbtype == 'month') {
    $title = "总";
    $cond = "DATE_SUB(CURDATE(), INTERVAL 365 DAY) <= date(time)";
  }
  ?>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="https://boom.dgehs.top"><img src="back.svg" width="30" height="30"/>返回</a>
        </li>
    </ol>
  </nav>
  <div class="page-header text-center">
    <h1>排行榜 - <?php echo $title; ?>榜</h1>
     <p><del>抢叮当生意的表白墙</del></p>
    <a href="?lbtype=day<?php echo $CurrentUser ? "&name=" . $CurrentUser : "" ?>"><button type="button" class="btn btn-outline-secondary btn-sm">日榜</button></a>
    <a href="?lbtype=week<?php echo $CurrentUser ? "&name=" . $CurrentUser : "" ?>"><button type="button" class="btn btn-outline-secondary btn-sm">月榜</button></a>
    <a href="?lbtype=month<?php echo $CurrentUser ? "&name=" . $CurrentUser : "" ?>"><button type="button" class="btn btn-outline-secondary btn-sm">总榜</button></a>
    <br/>
    <br/>
    注: 只有普通模式的分数才会参与排名。如果名字重复使用，只会记录第一个使用改名字的人的记录，并且只有打破自己保持的个人记录才会更新。
    <a href="https://pic.ke-lejun.xyz/2022/01/26/87ae02c3-1da6-41e6-b460-452977772866.mp4"> 恢复未保存记录 <a/>
    <br/>
    <br/>
  </div>
  <div class="list-group">
    <?php
    $rank = $offset;
    $filtercond = " ORDER BY score DESC limit ?,?;";
    $data_sql = "SELECT * FROM " . $ranking . " where " . $cond . $filtercond;
    if ($data_stmt = $link->prepare($data_sql)) {
      $data_stmt->bind_param("ii", $offset, $num);
      $data_stmt->execute();
      $data_stmt->store_result();
      $data_stmt->bind_result($id, $score, $name, $time, $system, $area, $message, $attempts);
      while ($data_stmt->fetch()) {
        $rank += 1;
        echo "<a href='#' class='list-group-item list-group-item-action'><div class='d-flex w-100 justify-content-between'>
            <h5 class='mb-1'>第" . $rank . "名  -  " . $score . " 分  -  " . $name . "</h5><small>" . $time . "</small></div>
            <p class='mb-1'>尝试了" . $attempts . "次 - " . $system . " - " . $area . "</p>
            <small>" . ($message ? $message : "这个人懒的一批，什么也不留，这么懒也不知道是怎么考上重点中学的") . "</small></a>";
      }
      $data_stmt->close();
    }
    ?>
    <nav aria-label="Page navigation example" style="margin-bottom:3em;">
      <ul class="pagination">
        <?php
        $rows_sql = "SELECT count(*) FROM " . $ranking . " where " . $cond;
        $rows_data = mysqli_query($link, $rows_sql);
        $rows = mysqli_fetch_row($rows_data)[0];
        if (!$result) {
                    printf("%s\n", mysqli_error($link));
                    exit();
                }
        $rows = $rows > $num * $max_pages ? $num * $max_pages : $rows;
        $total = ceil($rows / $num);
        if ($total > 1) {
          if ($CurrentPage > 1) {
            echo "<li class='page-item'><a class='page-link' href='?lbtype=" . $lbtype . "&page=" . ($CurrentPage - 1) . ($CurrentUser ? "&name=" . $CurrentUser : "") . "' aria-label='Previous'><span aria-hidden='true'>&laquo;</span></a></li>";
          }
          for ($p = 1; $p <= $total; $p++) {
            echo "<li class='page-item " . ($CurrentPage == $p ? "active" : "") . "'><a class='page-link' href='?lbtype=" . $lbtype . "&page=" . $p . ($CurrentUser ? "&name=" . $CurrentUser : "") . "'>" . $p . "</a></li>";
          }
          if ($total > $CurrentPage) {
            echo "<li class='page-item'><a class='page-link' href='?lbtype=" . $lbtype . "&page=" . ($CurrentPage + 1) . ($CurrentUser ? "&name=" . $CurrentUser : "") . "' aria-label='Next'><span aria-hidden='true'>&raquo;</span></a></li>";
          }
        }
        ?>
      </ul>
    </nav>
  </div>
  <footer class='fixed-bottom container'>
    <div class='row shadow bg-info rounded'>
      <div style='padding:0.2em 1em;'>
        <?php
        if ($CurrentUser) {
          $score_sql = "SELECT score,time,attempts FROM " . $ranking . " where name=?";
          $score_stmt = $link->prepare($score_sql);
          $score_stmt->bind_param("s", $CurrentUser);
          $score_stmt->bind_result($score, $time, $attempts);
          $score_stmt->execute();
          if ($score_stmt->fetch()) {
            echo $CurrentUser . " 的最高记录 已上传" . $attempts . "次<br/>" . "SCORE:" . $score . " " . $time;
          } else {
            echo "没有找到 " . $CurrentUser . " 的记录(或被过滤)";
          }
          $score_stmt->close();
        } else {
          echo "注意：你玩前还没有填名字";
        }
        $link->close();
        ?>
      </div>
    </div>
  </footer>
</body>

</html>