<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sockjs-client/1.5.2/sockjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/stomp.js/2.3.3/stomp.min.js"></script>
    <link rel="shortcut icon" type="image/png" href="assets/logo.png">
    <link rel="stylesheet" type="text/css" href="watch.css">
</head>
<body>
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
                <div id="chat" class="tabcontent active">Chat content goes here</div>
                <div id="video" class="tabcontent">Video content goes here</div>
            </article>
        </div>
        <div class="item participants">
            <h2>Participants</h2>
            <p><b>You</b></p>
        </div>
        <div class="item invite">
            You have joined the channel: 
            <p> <?php echo $_POST['channelid']; ?></p>
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

        const channelId = "<?php echo $_POST['channelid']; ?>" ; 
        const userId = "<?php echo $_POST['useridinput']; ?>" ; 
        const movieId = "Uncharted" ; 
        document.querySelector(".title-bar").innerHTML += `<h2 class="user">Hi ${userId}!</h2>`;

        document.addEventListener('DOMContentLoaded', () => {
            const myvideo = document.getElementById('myVideo');
            var stompClient = null;

            var socket = new SockJS(`http://127.0.0.1:8090/app`);
            stompClient = Stomp.over(socket);
            stompClient.connect({}, function (frame) {
                console.log('Connected: ' + frame);
                
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
                        response.json().then(data => {
                            console.log('Active Users in Channel:', data.activeUsersInChannel);
                            const participantsDiv = document.querySelector('.participants');
                            data.activeUsersInChannel.forEach(user => {
                                const p = document.createElement('p');
                                p.textContent = user;
                                participantsDiv.appendChild(p);
                            });
                        });

                        const message = { 'syncEventType': "PARTICIPANT_JOINED",'userId':userId,'currentTime': new Date().getTime()};
                        stompClient.send(`/${channelId}`, {}, JSON.stringify(message));

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
                        });
                    } else {
                        console.error('Authorization Failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
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
                // window.location.href = 'index.html';
            });
        });

    </script>
</body>
</html>