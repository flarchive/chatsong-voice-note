<?php

use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->content(function ($document) {
            $document->foot[] = '
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const observer = new MutationObserver(function() {
                    const toolbar = document.querySelector(".FlatrateComposer-toolbar, .flatrateComposer-toolbar, .TextEditor-controls");
                    if (toolbar && !document.querySelector(".chatsong-voice-btn")) {
                        const li = document.createElement("li");
                        li.className = "Composer-control";

                        const btn = document.createElement("button");
                        btn.className = "Button Button--icon chatsong-voice-btn";
                        btn.type = "button";
                        btn.innerHTML = "<i class=\"fas fa-microphone\"></i>";
                        btn.title = "Spreek een voice-note in";
                        btn.style.cssText = "color:#e11d48; background:rgba(225,29,72,0.1); border:none; border-radius:4px; padding:6px 10px; cursor:pointer; margin-right:5px;";

                        let mediaRecorder;
                        let audioChunks = [];
                        let isRecording = false;

                        btn.onclick = async () => {
                            const textarea = document.querySelector(".TextEditor-editor textarea");
                            if (!textarea) return;

                            if (!isRecording) {
                                try {
                                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                                    mediaRecorder = new MediaRecorder(stream);
                                    audioChunks = [];
                                    mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
                                    mediaRecorder.onstop = () => {
                                        const audioBlob = new Blob(audioChunks, { type: "audio/webm" });
                                        const audioUrl = URL.createObjectURL(audioBlob);
                                        const start = textarea.selectionStart;
                                        const text = textarea.value;
                                        textarea.value = text.substring(0, start) + "\n🎵 [Voice Note](" + audioUrl + ")\n" + text.substring(textarea.selectionEnd);
                                        textarea.dispatchEvent(new Event("input", { bubbles: true }));
                                    };
                                    mediaRecorder.start();
                                    isRecording = true;
                                    btn.style.color = "#ffffff";
                                    btn.style.background = "#e11d48";
                                } catch (err) {
                                    alert("Geen toegang tot microfoon.");
                                }
                            } else {
                                mediaRecorder.stop();
                                isRecording = false;
                                btn.style.color = "#e11d48";
                                btn.style.background = "rgba(225,29,72,0.1)";
                            }
                        };

                        li.appendChild(btn);
                        toolbar.prepend(li);
                    }
                });

                observer.observe(document.body, { childList: true, subtree: true });
            });
            </script>';
        }),
];
