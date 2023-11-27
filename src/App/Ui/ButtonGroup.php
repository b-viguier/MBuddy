<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Ui;

use Bveing\MBuddy\Ui\Component;
use Bveing\MBuddy\Ui\JsEventBus;

class ButtonGroup implements Component
{
    public function __construct(
        private JsEventBus $jsEventBus,
    ){

    }

    public function render(): string
    {
        return <<<HTML
                <div class="row no-gutters">
                    <div class="col btn-group" role="group">
                        <button type="button" class="btn btn-primary border-dark"><i class="bi bi-card-image"></i></button>
                        <button type="button" class="btn btn-primary border-dark"><i class="bi bi-floppy-fill"></i></button>
                        <button type="button" class="btn btn-primary border-dark"><i class="bi bi-copy"></i></button>
                        <button type="button" class="btn btn-warning border-dark" data-toggle="modal" data-target="#confirmResetModal"><i class="bi bi-trash3-fill"></i></button>
                    </div>
                </div>


                <!-- Modal -->
                <div class="modal fade" id="confirmResetModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body bg-secondary">
                                This action will reset current Preset (Master + Song)
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-success">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            HTML;
    }
}
