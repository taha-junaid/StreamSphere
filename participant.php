<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sockjs-client/1.5.2/sockjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/stomp.js/2.3.3/stomp.min.js"></script>
    <script defer src="https://unpkg.com/peerjs@1.4.7/dist/peerjs.min.js"></script>
    <link rel="shortcut icon" type="image/png" href="assets/logo.png">
    <link rel="stylesheet" type="text/css" href="watch.css">
</head>
<body>
    <div id="overlay"></div>
    <div class="title-bar">
        <img src="assets/logo.png" width="60px" height="50px" style="margin-top: 8px;">
        <h1>StreamSphere</h1>
    </div>
    <div class="main">
        <div class="item">
            <video id="myVideo">
                <source src="https://d2s1ugs0wx5860.cloudfront.net/uncharted-trailer.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="item">
            <nav class="tabnav">
                <div class="tab active" data-target="chat">Chat</div>
                <div class="tab" data-target="video">Video</div>
            </nav>
            <article class="tabcontainer">
                <div id="chat" class="tabcontent active">
                    <div class="chat-messages-container">
                        <ul id="chat-messages">
                            <!-- messages -->
                        </ul>
                    </div>
                    <form id="chat-form" class="bottom">
                      <input type="text" id="message-input" placeholder="Type a message...">
                      <button id="message-send" type="submit">Send</button>
                    </form>
                  </div>
                <div id="video" class="tabcontent">
                    <div id = "video-grid"> </div>
                </div>
            </article>
        </div>
        <div class="item participants">
            <h2>Participants</h2>
            <p><b>You</b></p>
        </div>
        <div class="item invite">
            You have joined the channel: 
            <p> <?php echo $_POST["channelid"]; ?></p>
            <button id="leave-btn">Leave</button>
        </div>
    </div>
    <script>
        const logo = document.getElementsByClassName('title-bar')[0];
            logo.addEventListener('click', () => {
            window.location.href = 'index.html';
        });
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(tab => tab.classList.remove('active'));
                tab.classList.add('active');
                const tabcontents = document.querySelectorAll('.tabcontent');
                tabcontents.forEach(tabcontent => tabcontent.classList.remove('active'));
                const target = tab.getAttribute('data-target');
                document.querySelector(`#${target}`).classList.add('active');
            });
        });
        const urlParams = new URLSearchParams(window.location.search);

        const channelId = "<?php echo $_POST["channelid"]; ?>" ; 
        const userId = "<?php echo $_POST["useridinput"]; ?>" ; 
        const movieId = "Uncharted" ; 
        document.querySelector(".title-bar").innerHTML += `<h2 class="user">Hi ${userId}!</h2>`;
        const joinurl = 'http://127.0.0.1:8090/join';

        const params = new URLSearchParams();
        params.append('userId', userId);
        params.append('channelId', channelId);
        params.append('movieId', movieId);

        fetch(joinurl, {
            method: 'POST',
            body: params
        })
        .then(response => {
            if (response.ok) {
                console.log('Authorization Success');
                const overlay = document.getElementById('overlay');
                overlay.style.display = 'none';
                


                response.json().then(data => {
                    console.log('Active Users in Channel:', data.activeUsersInChannel);
                    const participantsDiv = document.querySelector('.participants');
                    data.activeUsersInChannel.forEach(user => {
                        const p = document.createElement('p');
                        p.textContent = user;
                        participantsDiv.appendChild(p);
                    });
                });

                // document.addEventListener('DOMContentLoaded', () => {
                const myvideo = document.getElementById('myVideo');
                var stompClient = null;

                var socket = new SockJS(`http://127.0.0.1:8090/app`);
                stompClient = Stomp.over(socket);
                stompClient.connect({}, function (frame) {
                    console.log('Connected: ' + frame);
                    const message = { 'syncEventType': "PARTICIPANT_JOINED",'userId':userId,'currentTime': new Date().getTime()};
                stompClient.send(`/${channelId}`, {}, JSON.stringify(message));
                //vvvvvvvvvvvvv    VIDEO CONF    vvvvvvvvvvvvvvvv
                const videoGrid = document.getElementById('video-grid')


                

                const myPeer = new Peer(undefined, {
                    host: '/',
                    port: '3001'
                })

                const myVideo = document.createElement('video')
                myVideo.muted = true
                const peers = {}
                navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                }).then(stream => {
                    addVideoStream(myVideo, stream)

                    myPeer.on('call', call => {
                        call.answer(stream)
                        const video = document.createElement('video')
                        call.on('stream', userVideoStream => {
                            console.log("mediastream")
                            addVideoStream(video, userVideoStream)
                        })
                    })

                    stompClient.subscribe(`/channel/${channelId}`, function (syncEvent) {
                        var syncEventType = JSON.parse(syncEvent.body).syncEventType
                        // console.log(host.currentTime + "-------" + host.videoTimeStamp + host.paused)
                        if(syncEventType=="TIME_SYNC"){
                            var host = JSON.parse(syncEvent.body)
                            const hostCurrentTime = host.currentTime; //at 6:30:00 pm in epoch | 
                            const hostVideoTimeStamp = parseFloat(host.videoTimeStamp); //5.12 sec of the video

                            const clientCurrentTime = new Date().getTime(); //6:30:07 pm in epoch
                            const clientVideoTimeStamp = parseFloat(myvideo.currentTime)//1.1 sec
                            // console.log("clientVideoTimeStamp     " + clientVideoTimeStamp)

                            const liveTimeDiff = (clientCurrentTime - parseFloat(hostCurrentTime)) / 1000; //7 sec
                            // console.log("liveTimeDiff    " + liveTimeDiff);
                            const hostVideoTimeStampAtClientLiveTime = hostVideoTimeStamp + liveTimeDiff; //5.19 s
                            // console.log("hostVideo    " + hostVideoTimeStampAtClientLiveTime);
                            const videoTimeStampDiff = hostVideoTimeStampAtClientLiveTime - clientVideoTimeStamp;
                            // console.log("videoTimeStampDiff    " + videoTimeStampDiff);

                            // console.log(host.paused +"<-host partic->"+myvideo.paused)
            
                            if (myvideo.paused == false && host.paused == "true") {
                                console.log("i stopped")
                                myvideo.currentTime = myvideo.currentTime + videoTimeStampDiff;
                                myvideo.pause();
                            }

                            if (myvideo.paused == true && host.paused == "false") {
                                console.log("i started")
                                myvideo.currentTime = myvideo.currentTime + videoTimeStampDiff;
                                myvideo.play();
                            }

                            if (Math.abs(videoTimeStampDiff) > 5) {
                                myvideo.currentTime = myvideo.currentTime + videoTimeStampDiff;
                            }

                        } else if(syncEventType=="PARTICIPANT_JOINED"){
                            var newparticipant = JSON.parse(syncEvent.body)
                            const participantsDiv = document.querySelector('.participants');
                            if(newparticipant.userId != userId)
                                participantsDiv.innerHTML += `<p>${newparticipant.userId}<p>`;
                        } else if(syncEventType=="PARTICIPANT_LEFT"){
                            var participant = JSON.parse(syncEvent.body)
                            const participantsDiv = document.querySelector('.participants'); 
                            const participants = participantsDiv.querySelectorAll('p'); 
                            const searchParticipant = participant.userId;
                            participants.forEach((p) => { 
                                if (p.textContent === searchParticipant) { 
                                    participantsDiv.removeChild(p); 
                                }
                            });
                        } 
                        else if(syncEventType == "VC_PARTICIPANT_JOINED"){
                            var userId = JSON.parse(syncEvent.body).userId
                            console.log("*** participant userid", userId)
                            setTimeout(connectToNewUser, 1000, userId, stream)
                        }
                    })
                    

                });
                // stompClient.subscribe(`/vcrec/${channelId}`, function (response) {

                // var userId = JSON.parse(response.body).userId
                // console.log("participant userid", userId)
                // setTimeout(connectToNewUser, 1000, userId, stream)

                // })
                myPeer.on('open', id => {
                    const message = { 'syncEventType': "VC_PARTICIPANT_JOINED", 'currentTime': new Date().getTime(), 'userId': id };
                    stompClient.send(`/${channelId}`, {}, JSON.stringify(message));  
                    console.log("myID=", id);
                })


                function connectToNewUser(userId, stream) {
                    const call = myPeer.call(userId, stream)
                    const video = document.createElement('video')
                    call.on('stream', userVideoStream => {
                        console.log("newuserstream")
                        addVideoStream(video, userVideoStream)
                    })
                    call.on('close', () => {
                        video.remove()
                    })

                    peers[userId] = call
                }

                function addVideoStream(video, stream) {
                    video.srcObject = stream
                    video.classList.add('vcvideo');
                    video.addEventListener('loadedmetadata', () => {
                        video.play()
                    })
                    videoGrid.append(video)
                }
                //^^^^^^^^^^^^^^    VIDEO CONF    ^^^^^^^^^^^^^^^^

                
            });
                

                const leaveBtn = document.getElementById('leave-btn');
                    leaveBtn.addEventListener('click', () => {
                        const joinurl = 'http://127.0.0.1:8090/leave';
                        const params = new URLSearchParams();
                        params.append('userId', userId);
                        params.append('channelId', channelId);
                        params.append('movieId', movieId);

                        fetch(joinurl, {
                            method: 'POST',
                            body: params
                        }).then(response => {
                                const message = { 'syncEventType': "PARTICIPANT_LEFT",'userId':userId,'currentTime': new Date().getTime()};
                                stompClient.send(`/${channelId}`, {}, JSON.stringify(message));
                                console.log("userId left channel")
                        }).catch(error => {
                                console.log("failed to leave channel")
                        });
                    window.location.href = 'index.html';
                });

                //vvvvvvvvvvvvv    TEXT CHAT    vvvvvvvvvvvvvvvv
                const chat_socket = new WebSocket(`wss://fgw6trlhnj.execute-api.us-east-1.amazonaws.com/production/?userID=${userId}&channelID=${channelId}`);

                // Handle incoming messages from WebSocket server
                chat_socket.addEventListener('message', event => {
                    const message = JSON.parse(event.data);
                    const userId = message.userId;
                    const text = message.message;

                    const chatMessages = document.getElementById('chat-messages');
                    const messageContainer = document.createElement('div');
                    const newMessage = document.createElement('li');
                    newMessage.textContent = `${userId}: ${text}`;
                    newMessage.classList.add('message-bubble', 'received-message');

                    messageContainer.appendChild(newMessage);
                    messageContainer.classList.add('message-container');
                    chatMessages.appendChild(messageContainer);

                    const chatMessagesContainer = document.getElementsByClassName('chat-messages-container')[0];
                    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
                });

                // Handle form submit event to send messages to WebSocket server
                const messageForm = document.getElementById('chat-form');
                messageForm.addEventListener('submit', event => {
                    event.preventDefault();

                    const messageInput = document.getElementById('message-input');
                    const message = messageInput.value.trim();
                    const action = "sendMessage"
                    if (message === '') {
                        return;
                    }

                    const data = JSON.stringify({ action, message, userId });
                    chat_socket.send(data);

                    messageInput.value = '';

                    const chatMessages = document.getElementById('chat-messages');
                    const messageContainer = document.createElement('div');
                    const newMessage = document.createElement('li');
                    newMessage.textContent = `You: ${message}`;
                    newMessage.classList.add('message-bubble', 'sent-message');
                    messageContainer.appendChild(newMessage);
                    messageContainer.classList.add('message-container');
                    chatMessages.appendChild(messageContainer);

                    const chatMessagesContainer = document.getElementsByClassName('chat-messages-container')[0];
                    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
                });
                //^^^^^^^^^^^^^^    TEXT CHAT    ^^^^^^^^^^^^^^^^
            // });
            }
            else if (response.status === 404) {
                const message = 'Channel does not exist. Click OK to redirect to the homepage.';
                window.alert(message);
                window.location.href = '/'; 
            } else {
                console.error('Authorization Failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });

    </script>
</body>
</html>