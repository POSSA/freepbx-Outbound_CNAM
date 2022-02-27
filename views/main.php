<div class="container-fluid">
    <h1><?php echo _('Outbound CallerID Name') ?></h1>
    <?php echo $updateNotice ?>
    <div class="display full-border">
        <div class="fpbx-container">
            <div class="display full-border">
                <form class="fpbx-submit" name="frm_outcid" action="config.php?display=outcnam" method="post" role="form">
                    <input type="hidden" name="action" value="edit">
                    <!--Enable CDR-->
                    <div class="element-container">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label" for="enable_cdr"><?php echo _("Enable CDR") ?></label>
                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="enable_cdr"></i>
                                </div>
                                <div class="col-md-9">
                                    <span class="radioset">
                                        <input type="radio" name="enable_cdr" id="enable_cdryes" value="CHECKED" <?php echo $enable_cdr === "CHECKED" ? "CHECKED" : ''  ?>>
                                        <label for="enable_cdryes"><?php echo _("Yes"); ?></label>
                                        <input type="radio" name="enable_cdr" id="enable_cdrno" value="" <?php echo $enable_cdr === "CHECKED" ? "" : "CHECKED" ?>>
                                        <label for="enable_cdrno"><?php echo _("No"); ?></label>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span id="enable_cdr-help" class="help-block fpbx-help-block"><?php echo _("If enabled, the CDR AccountCode column will be populated with any found outbound CNAM.") ?></span>
                            </div>
                        </div>
                    </div>
                    <!--END Enable CDR-->
                    <!--Enable RPID-->
                    <div class="element-container">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label" for="enable_rpid"><?php echo _("Enable RPID") ?></label>
                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="enable_rpid"></i>
                                </div>
                                <div class="col-md-9">
                                    <span class="radioset">
                                        <input type="radio" name="enable_rpid" id="enable_rpidyes" value="CHECKED" <?php echo $enable_rpid === "CHECKED" ? "CHECKED" : ''  ?>>
                                        <label for="enable_rpidyes"><?php echo _("Yes"); ?></label>
                                        <input type="radio" name="enable_rpid" id="enable_rpidno" value="" <?php echo $enable_rpid === "CHECKED" ? "" : "CHECKED" ?>>
                                        <label for="enable_rpidno"><?php echo _("No"); ?></label>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span id="enable_rpid-help" class="help-block fpbx-help-block"><?php echo _("If enabled, found CNAM will be displayed on rpid enabled endpoints.") ?></span>
                            </div>
                        </div>
                    </div>
                    <!--END Enable RPID-->
                    <!--Scheme-->
                    <div class="element-container">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label" for="scheme"><?php echo _("Scheme") ?></label>
                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="scheme"></i>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control" id="scheme" name="scheme">
                                        <?php echo $schemeOptions ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span id="scheme-help" class="help-block fpbx-help-block"><?php echo _("Setup Schemes in CID Superfecta section") ?></span>
                            </div>
                        </div>
                    </div>
                    <!--END Scheme-->
                </form>
            </div>
        </div>
    </div>
</div>