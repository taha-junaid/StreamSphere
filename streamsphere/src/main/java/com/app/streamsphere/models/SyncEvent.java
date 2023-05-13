package com.app.streamsphere.models;

import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
public class SyncEvent {
    private syncEventType syncEventType; //TIME_SYNC , PARTICIPANT_JOINED, PARTICIPANT_LEFT, VC_PARTICIPANT_JOINED, VC_PARTICIPANT_LEFT
    private String userId="";
    private String currentTime;
    private String videoTimeStamp="";
    private String paused="";

    public SyncEvent(syncEventType syncEventType, String userId, String currentTime){
        this.syncEventType = syncEventType;
        this.userId = userId;
        this.currentTime = currentTime;
    }

    public SyncEvent(syncEventType syncEventType, String currentTime, String videoTimeStamp, String paused){
        this.syncEventType = syncEventType;
        this.currentTime = currentTime;
        this.videoTimeStamp = videoTimeStamp;
        this.paused = paused;
    }

    public SyncEvent(){}
}
