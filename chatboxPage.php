<div class="container py-5">
    <div class="row d-flex justify-content-start">
        <div class="col-md-8 col-lg-6 col-xl-4">
            <div class="card" id="chat1" style="">
                <div
                    class="card-header d-flex justify-content-between align-items-center p-3 border-bottom-0"
                    style="">
                    <p class="mb-0 fw-bold" style = "margin: auto" >Chat</p>
                </div>
                <div class="card-body">
                    <div class="chatbox-messages" id="chatbox-messages">
                    </div> 
                    <div class="form-outline">
                        <textarea class="form-control" id="chatboxMessage" rows="4"></textarea>
                        <label class="form-label" for="textAreaExample">Nachricht</label>
                    </div>
                    <button type="button" class="btn btn-outline-secondary ui-button mb-2" onclick="enterChatboxMessage(true)">
                        Senden
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>