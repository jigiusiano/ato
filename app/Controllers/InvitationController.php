<?php

namespace App\Controllers;

use App\Models\InvitationModel;
use App\Utils\Response;
use App\Controllers\Validators\InvitationValidator;
use App\Models\TaskModel;
use App\Models\UserModel;
use App\Controllers\EmailSenderController;

class InvitationController
{
    private InvitationModel $invitationModel;
    private InvitationValidator $invitationValidator;
    private EmailSenderController $emailSenderController;
    private TaskModel $taskModel;
    private UserModel $userModel;
    private Response $res;

    public function __construct()
    {
        $this->invitationModel = new InvitationModel();
        $this->invitationValidator = new InvitationValidator();
        $this->emailSenderController = new EmailSenderController();
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();
        $this->res = new Response();
    }

    private function getBody(string $recipientName = '', string $inviterName = '', string $inviterEmail = '', string $taskName = '', string $taskDescription = '', string $taskExpirationDate, string $acceptUrl = '', string $cancelUrl = ''): string
    {
        $body = <<<EOD
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <title>Invitación a una nueva tarea</title>
            <style type="text/css">
                body, table, td, p, a, li, blockquote {
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
                }
                table, td {
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
                }
                img {
                -ms-interpolation-mode: bicubic;
                border: 0;
                height: auto;
                outline: none;
                text-decoration: none;
                }
                body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                }
                img {
                max-width: 100%;
                }
                #outlook a {
                padding: 0;
                }
                .ReadMsgBody {
                width: 100%;
                }
                .ExternalClass {
                width: 100%;
                }
                .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
                line-height: 100%;
                }
            </style>
            </head>
            <body style="background-color: #f7f7f7; margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; font-family: 'Roboto', Arial, sans-serif;">
            <div style="background-color: #f7f7f7;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f7f7f7;">
                <tr>
                    <td align="center" style="padding: 30px 0;">
                    <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                        <tr>
                        <td align="center" bgcolor="#3366CC" style="padding: 30px 30px 20px 30px;">
                            <h1 style="color: #ffffff; font-family: 'Roboto', Arial, sans-serif; font-weight: 600; font-size: 24px; margin: 20px 0 0 0; text-align: center;">Has sido invitado a una nueva tarea</h1>
                        </td>
                        </tr>
                        
                        <tr>
                        <td align="left" style="padding: 30px; font-family: 'Roboto', Arial, sans-serif; font-size: 16px; line-height: 24px; color: #555555;">
                            <p style="margin: 0 0 20px 0;">Hola $recipientName,</p>
                            <p style="margin: 0 0 20px 0;">$inviterName ($inviterEmail) te invita a participar en la tarea $taskName en la plataforma ATO. Por favor, confirma tu participación lo antes posible.</p>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8f9fa; border-left: 4px solid #3366CC; border-radius: 4px; margin: 20px 0;">
                            <tr>
                                <td style="padding: 20px;">
                                <h2 style="color: #333333; font-family: 'Roboto', Arial, sans-serif; font-size: 18px; font-weight: 600; margin: 0 0 15px 0;">$taskName</h2>
                                <p style="margin: 0 0 15px 0; color: #555555; font-size: 15px;">$taskDescription</p>
                                <p style="margin: 0; color: #777777; font-size: 14px;"><strong>Fecha de vencimiento: </strong>$taskExpirationDate</p>
                                </td>
                            </tr>
                            </table>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 30px 0;">
                            <tr>
                                <td align="center">
                                <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                    <tr>
                                    <td align="center" width="50%" style="padding: 0 10px;">
                                        <a href="$acceptUrl" style="background-color: #28a745; border-radius: 4px; color: #ffffff; display: inline-block; font-family: 'Roboto', Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 45px; text-align: center; text-decoration: none; width: 100%; -webkit-text-size-adjust: none;">Aceptar Tarea</a>
                                    </td>
                                    <td align="center" width="50%" style="padding: 0 10px;">
                                        <a href="$cancelUrl" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #555555; display: inline-block; font-family: 'Roboto', Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 45px; text-align: center; text-decoration: none; width: 100%; -webkit-text-size-adjust: none;">Cancelar Tarea</a>
                                    </td>
                                    </tr>
                                </table>
                                </td>
                            </tr>
                            </table>
                        </td>
                        </tr>
                        
                        <tr>
                        <td align="center" bgcolor="#f8f9fa" style="padding: 20px; border-top: 1px solid #eeeeee;">
                            <p style="color: #777777; font-family: 'Roboto', Arial, sans-serif; font-size: 13px; margin: 0;">© 2025 ATO. Todos los derechos reservados.</p>
                        </td>
                        </tr>
                    </table>
                    </td>
                </tr>
                </table>
            </div>
            </body>
            </html>
        EOD;

        return $body;
    }

    private function sendEmail($ownerData, $task, $recipientUserData, $invitation): Response
    {
        $wasSent = $this->emailSenderController->sendEmail(
            $recipientUserData[0]["email"],
            $task[0]["subject"],
            $this->getBody(
                $recipientUserData[0]["name"] . " " . $recipientUserData[0]["surname"],
                $ownerData[0]["name"] . " " . $ownerData[0]["surname"],
                $ownerData[0]["email"],
                $task[0]["subject"],
                $task[0]["description"],
                $task[0]["expiration_date"],
                base_url() . "invitations/" . $invitation[0]["ID_invitation"] . "?recipient=" . $recipientUserData[0]["ID_user"] . "&stat=2",
                base_url() . "invitations/" . $invitation[0]["ID_invitation"] . "?recipient=" . $recipientUserData[0]["ID_user"] . "&stat=3"
            )
        );

        if (!$wasSent) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";

            return $this->res;
        }

        $this->res->code = 201;
        $this->res->message = "La invitación se ha hecho con éxito";

        return $this->res;
    }

    public function create($invitationData): Response
    {
        $this->res = $this->invitationValidator->validateData($invitationData);

        if (!$this->res->areDataValid) {
            return $this->res;
        }

        try {
            $recipientUserData = $this->userModel->getUserByEmail($invitationData->recipient);

            if (count($recipientUserData) == 0) {
                $this->res->code = 500;
                $this->res->message = "No existe el usuario";

                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";

            return $this->res;
        }

        try {
            $task = $this->taskModel->getById($invitationData->task);

            if (count($task) == 0) {
                $this->res->code = 500;
                $this->res->message = "No existe la tarea";

                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";

            return $this->res;
        }

        try {
            $ownerData = $this->userModel->getById($task[0]["owner"]);

            if (count($ownerData) == 0) {
                $this->res->code = 500;
                $this->res->message = "No existe el propietario de la tarea";

                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";
        }

        try {
            $invitation = $this->invitationModel->getByUserAndTask(
                $recipientUserData[0]["ID_user"],
                $invitationData->task
            );

        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";
        }

        if (count($invitation) > 0) {
            $this->res = $this->sendEmail(
                $ownerData,
                $task,
                $recipientUserData,
                $invitation
            );

            return $this->res;
        }

        try {
            $this->invitationModel->create(
                $recipientUserData[0]["ID_user"],
                $invitationData->task
            );

            try {
                $invitation = $this->invitationModel->getByUserAndTask(
                    $recipientUserData[0]["ID_user"],
                    $invitationData->task
                );
            } catch (\Throwable $th) {
                $this->res->code = 500;
                $this->res->message = "Ocurrio un error al realizar la invitación";
            }
            
            $this->res = $this->sendEmail(
                $ownerData,
                $task,
                $recipientUserData,
                $invitation
            );

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";

            return $this->res;
        }
    }

    public function update(string $id, string $stat, string $recipientId): Response
    {
        try {
            $invitation = $this->invitationModel->getById($id);

            if (count($invitation) == 0) {
                $this->res->code = 404;
                $this->res->message = "La invitación no existe";

                return $this->res;
            }

            if ($invitation[0]["recipient"] != $recipientId) {
                $this->res->code = 403;
                $this->res->message = "No tienes permisos para actualizar el estado de esta invitación";

                return $this->res;
            }

            $this->res = $this->invitationValidator->validateData($stat);

            if (!$this->res->areDataValid) {
                return $this->res;
            }

            try {
                if ($stat == 2) {
                    $this->invitationModel->acceptInvitation($id, $stat);
                } else {
                    $this->invitationModel->rejectInvitation($id, $stat);
                }

                $this->res->code = 200;
                $this->res->message = "Operación exitosa";

                return $this->res;
            } catch (\Throwable $th) {
                $this->res->code = 500;
                $this->res->message = "Ocurrio un error al actualizar el estado de la invitación";

                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar el usuario";

            return $this->res;
        }
    }
}
