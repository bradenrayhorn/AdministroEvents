<?php
    // Load events
    $administro->plugins['Events']->loadEvents();
    $events = $administro->plugins['Events']->events;
    // Generate nonce
    $addEventNonce = $administro->generateNonce('addevent');
?>
<div class='title'>
    Events
</div>
<div class='spacer'></div>
<div class='events'>
<?php
    echo $administro->plugins['Events']->eventDisplay(false, true);
?>
</div>
<div class='title sub'>
    Add Event
</div>
<div>
    <form method='post' action='<?php echo $administro->baseDir . 'form/addevent' ?>' enctype='multipart/form-data'>
        <div class='row'>
            <div class='two columns'>
                <label>Name *</label>
                <input type='text' name='name' required>
            </div>
            <div class='two columns'>
                <label>Link</label>
                <input type='url' name='link'>
            </div>
        </div>
        <div class='row'>
            <div class='one column'>
                <label>Day *</label>
                <select name='day'>
                    <?php
                        for($i = 1; $i <= 31; $i++) {
                            echo '<option ' . sprintf("%04d", $i) . '>' . $i . '</option>';
                        }
                    ?>
                </select>
            </div>
            <div class='one column'>
                <label>Month *</label>
                <select name='month'>
                    <option value='01'>January</option>
                    <option value='02'>February</option>
                    <option value='03'>March</option>
                    <option value='04'>April</option>
                    <option value='05'>May</option>
                    <option value='06'>June</option>
                    <option value='07'>July</option>
                    <option value='08'>August</option>
                    <option value='09'>September</option>
                    <option value='10'>October</option>
                    <option value='11'>November</option>
                    <option value='12'>December</option>
                </select>
            </div>
            <div class='one column'>
                <label>Year *</label>
                <select name='year'>
                    <?php
                        $year = date("Y");
                        for($y = $year; $y < ($year + 5); $y++) {
                            echo '<option value="' . $y . '">' . $y . '</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class='row'>
            <label>File</label>
            <input type="file" name="file">
        </div>
        <input type='hidden' name='nonce' value='<?php echo $addEventNonce; ?>'>
        <input class="button-primary" type="submit" value="Add Event">
    </form>
</div>
<style>
    .events a {
        color: black;
        text-decoration: none;
    }
    .events a:hover {
        text-decoration: underline;
    }
</style>
