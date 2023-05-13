package com.app.streamsphere.controller;

import com.app.streamsphere.models.SyncEvent;
import com.app.streamsphere.models.syncEventType;
import org.springframework.messaging.handler.annotation.MessageMapping;
import org.springframework.messaging.handler.annotation.SendTo;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.CrossOrigin;

@Controller
public class SyncController {

    @MessageMapping("/{channelId}")
    @SendTo("/channel/{channelId}")
    @CrossOrigin(origins = "*")
    public SyncEvent syncEvent(SyncEvent payload) throws Exception {
        syncEventType syncEventType = payload.getSyncEventType();
        if (syncEventType == syncEventType.TIME_SYNC){
            return new SyncEvent(syncEventType,payload.getCurrentTime(),payload.getVideoTimeStamp(),payload.getPaused());
        } else if (syncEventType == syncEventType.PARTICIPANT_JOINED || syncEventType == syncEventType.PARTICIPANT_LEFT){
            return new SyncEvent(syncEventType,payload.getUserId(),payload.getCurrentTime());
        } else if (syncEventType == syncEventType.VC_PARTICIPANT_JOINED || syncEventType == syncEventType.VC_PARTICIPANT_LEFT) {
            return new SyncEvent(syncEventType, payload.getUserId(), payload.getCurrentTime());
        } else
            throw new RuntimeException("Unknown syncEventType");
    }
}
