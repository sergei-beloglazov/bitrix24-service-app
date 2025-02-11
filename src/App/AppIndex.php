<?php

namespace Bitrix24Integration\App;

use Bitrix24Integration\Lib\CRest;

?><html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="css/app.css?ver=v5.3.3">
    <!--link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"-->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="//api.bitrix24.com/api/v1/dev/"></script>
    <title>ServiceApp</title>
    <style>
        body {
            color: rgb(83, 92, 105);
        }

        .avatar {
            min-width: 35px;
        }

        .name {
            min-width: 305px;
        }

        .avatar-loaded {
            background-position: 0px 0px !important;
            background-size: contain !important;
        }

        .avatar-image-default {
            display: inline-block;
            overflow: hidden;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #535c6a url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2247.188%22%20height%3D%2254.063%22%20viewBox%3D%220%200%2047.188%2054.063%22%3E%3Cdefs%3E%3Cstyle%3E.cls-1%20%7B%20fill%3A%20%23fff%3B%20fill-rule%3A%20evenodd%3B%20%7D%3C/style%3E%3C/defs%3E%3Cpath%20id%3D%22Shape_2_copy_4%22%20data-name%3D%22Shape%202%20copy%204%22%20class%3D%22cls-1%22%20d%3D%22M47.18%2054.062c0-3.217-3.61-16.826-3.61-16.826%200-1.99-2.6-4.26-7.72-5.585a17.394%2017.394%200%200%201-4.887-2.223c-.33-.188-.28-1.925-.28-1.925l-1.648-.25c0-.142-.14-2.225-.14-2.225%201.972-.663%201.77-4.574%201.77-4.574%201.252.695%202.068-2.4%202.068-2.4%201.482-4.3-.738-4.04-.738-4.04a27.05%2027.05%200%200%200%200-7.918c-.987-8.708-15.847-6.344-14.085-3.5-4.343-.8-3.352%209.082-3.352%209.082l.942%202.56c-1.85%201.2-.564%202.65-.5%204.32.09%202.466%201.6%201.955%201.6%201.955.093%204.07%202.1%204.6%202.1%204.6.377%202.556.142%202.12.142%202.12l-1.786.217a7.1%207.1%200%200%201-.14%201.732c-2.1.936-2.553%201.485-4.64%202.4-4.032%201.767-8.414%204.065-9.193%207.16S-.012%2054.06-.012%2054.06h47.19z%22/%3E%3C/svg%3E) no-repeat center;
            background-size: 15px;
        }

        .manager:not(:last-child) {
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }

        .button-save {
            text-transform: uppercase;
        }

        [data-manager-selector] {
            cursor: pointer;
        }

        .manager--selected {
            background-color: rgb(255, 254, 239);
        }

        .form-control[data-manager-name] {
            caret-color: transparent;
        }

        .form-control[data-manager-name]:focus {
            box-shadow: none;

        }
    </style>
</head>

<body>
    <form id="service-app-settings">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">
                    <h2>ServiceApp settings</h2>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-12 col-xs-12 col-md-7 col-lg-9">
                    <?php
                    //Data for showing in the form
                    $formData = [];
                    $loadResult = CRest::runLoadSettings();

                    //Check load settings success
                    if (!$loadResult["load"]) {
                        echo "<b>An error has occured: </b> <span style='color:red'>" . $loadResult["error"] . "</span><br><br>";
                    } else {
                        //Show loaded data
                        $formData = $loadResult["loadData"];
                        //Check POST data
                        if (!empty($_REQUEST["save"]) && $_REQUEST["save"] == "Y") {
                            $saveResult = CRest::runSaveSettings();
                            //Check save success
                            if (!$saveResult["save"]) {
                                echo "<b>An error has occured: </b> <span style='color:red'>" . $saveResult["error"] . "</span><br><br>";
                            } else {
                                echo "<span style='color:green'><b>The data was successfully saved</b></span><br><br>";
                                //Show entered data without reloading from DB
                                $formData["manager_id"] = intval($_REQUEST["manager_id"]);
                    ?>
                                <script>
                                    BX24.init(function() {
                                        //Close window
                                        BX24.closeApplication();
                                    });
                                </script>
                        <?
                            }
                        }
                        ?>
                        <input type="hidden" name="save" value="Y">
                        <input type="hidden" name="member_id" value="<?= htmlspecialchars($formData["member_id"])  ?>">
                        <div class="form-group">
                            <label for="service_app_token">ServiceApp token:</label>
                            <input type="text" class="form-control" name="service_app_token" id="service_app_token"
                                placeholder="Please enter your ServiceApp token" readonly
                                value="<?= htmlspecialchars($formData["service_app_token"]) ?>">
                        </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12 col-md-10 col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            Manager
                        </div>
                        <div class="card-body p-0">
                            <div class="row manager align-items-center m-0 py-2">
                                <div class="col-sm-12 col-xs-12 col-md-5 col-lg-5 name">
                                    <div class="row align-items-center">
                                        <div class="col-sm-1 col-xs-1 col-md-1 col-lg-1 avatar">
                                            <div class="align-middle avatar-image-default"
                                                id="manager_avatar_1"
                                                data-manager-selector
                                                data-manager-avatar></div>
                                        </div>
                                        <div class="col ">
                                            <input type="text" class="form-control"
                                                name="manager_name[1]" id="manager_name_1"
                                                placeholder="Select a manager" readonly
                                                data-manager-selector data-manager-name>
                                            <input type="hidden" name="manager_id[1]" id="manager_id_1"
                                                value="<?= $managerId  ?>"
                                                data-manager-selector data-manager-id>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-xs-12 col-md-3 col-lg-3">
                                    <button type="button" class="btn btn-md btn-success"
                                        data-manager-selector>Select a manager</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <div class="row my-3">
                <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">
                    <button type="submit" class="btn btn-primary btn-lg btn-save button-save">Send</button>
                <?php

                    } // /Check load settings success
                ?>
                </div>
            </div>

        </div>
    </form>

    <script>
        //Global object for manager selectors
        let managerSelector = {
            /** @var {object} The manager block */
            $managerBlock: null,
            /** @var {object} Selected user */
            selectedUser: null,

            /** Highlights selected manager */
            highlightManager() {
                this.clearHighlight()
                    //Highlight current manager
                    .$managerBlock.toggleClass("manager--selected", true);
                return this;
            },
            /** Clears highlight */
            clearHighlight() {
                //Clear highlight from all
                $(".manager").toggleClass("manager--selected", false);
                return this;
            },
            /** User selection init */
            selectUser(e) {
                //Check if selection is in progress
                if (this.$managerBlock != null) {
                    return;
                }
                //Save the manager block
                this.$managerBlock = $(e.target).closest(".manager");
                //Highlight
                this.highlightManager();
                BX24.selectUser((result) => this.applyUser(result));
            },
            /** Show user information */
            showUserInfo(userData) {

                //Avatar presence flag
                let hasAvatar = userData.photo.length > 0;
                //Avatar
                let $avatar = this.$managerBlock.find("[data-manager-avatar]");

                //Check avatar
                if (hasAvatar) {
                    //Show avatar
                    $avatar.css("background-image", 'url(' + userData.photo + ')');
                } else {
                    $avatar.css("background-image", "");
                }
                $avatar.toggleClass("avatar-loaded", hasAvatar);


                //Show full name
                this.$managerBlock.find("[data-manager-name]").val(userData.name);
            },
            /** Save selected user */
            applyUser(selectedUser) {
                //Save user ID
                this.$managerBlock.find("[data-manager-id]").val(selectedUser.id);
                //Shoe information
                this.showUserInfo(selectedUser);

                //Clear selection
                this.$managerBlock = null;
                //Switch off highlight
                this.clearHighlight();
                return;
            },
            /** Loads user data for saved managers*/
            loadUsers() {
                //A list of inputs with manager IDs
                let $managerInputList = $("[data-manager-selector][data-manager-id]");
                //Check
                if ($managerInputList.length == 0) {
                    return;
                }
                //Loop though managers
                for (let i = 0; i < $managerInputList.length; i++) {
                    let $managerInput = $($managerInputList[i]);
                    //Find a manager block
                    let $managerBlock = $managerInput.closest(".manager");
                    //Manager user ID
                    let managerId = parseInt($managerInput.val());
                    //Check if valid
                    if (!(managerId > 0)) {
                        continue;
                    }
                    let self = this;
                    BX24.callMethod('user.get', {
                        ID: managerId
                    }, function(result) {
                        if (result.error()) {
                            console.debug('Error: user.get(' + managerId + ') call returns:');
                            console.debug(result.error().ex);
                            return;
                        } else {
                            //Get data
                            let resultData = result.data();
                            //Check
                            if (resultData.length == 0) {
                                console.debug('Error: user data is empty for ID = ' + managerId);
                                return;
                            }
                            let userInfo = resultData[0];
                            //Imitate user selection
                            self.$managerBlock = $managerBlock;
                            //Prepare user data
                            let userData = {
                                photo: typeof userInfo.PERSONAL_PHOTO == 'undefined' ?
                                    "" : userInfo.PERSONAL_PHOTO,
                                name: [userInfo.NAME, userInfo.LAST_NAME].join(" "),
                            };

                            self.showUserInfo(userData);
                            //Clear selection
                            self.$managerBlock = null;

                        }
                    });

                } // /Loop though managers
            }

        };
        //Init
        BX24.init(function() {
            //Load user data for saved managers
            managerSelector.loadUsers();
            //User selection
            $(document).on("click", "[data-manager-selector]", (e) => managerSelector.selectUser(e));
            var size = BX24.getScrollSize();
            // BX24.resizeWindow(size.scrollWidth, 600);
            BX24.fitWindow();
        });
    </script>

</body>

</html>