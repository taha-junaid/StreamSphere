## Streamsphere backend

- This is a Spring boot app that will run on EC2  
- It manages channel creation, connections, and facilitates the host and participants to communicate to synchronize their video playback times as `SyncEvent`
- All channel events `ChannelEventType{CREATED, JOINED, LEFT}` are pushed to kafka.  
- consumer consumes events every 15 secs and pushes them to dynamoDB Table user Events

#### Pending Work:
- View participants in each channel on watch page
- show recommendations based on past activity for users on home page.

#### sample db data:
<img width="993" alt="image" src="https://user-images.githubusercontent.com/113802375/236645026-0d6469ee-4fd5-47d3-9be1-76453f1062ec.png">


