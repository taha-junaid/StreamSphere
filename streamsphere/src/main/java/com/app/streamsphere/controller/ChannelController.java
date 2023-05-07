package com.app.streamsphere.controller;

import java.time.Instant;
import java.util.UUID;

import com.app.streamsphere.Kafka.UserEventPublisher;
import com.app.streamsphere.models.ChannelEventType;
import com.app.streamsphere.models.ChannelId;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

@RestController
@CrossOrigin(origins = "*")
public class ChannelController{
    @Autowired
    private UserEventPublisher userEventPublisher;

    @GetMapping("/createchannel/{userId}/{movie}")
    public ChannelId createChannel(@PathVariable String userId, @PathVariable String movie) {
        String channelId = UUID.randomUUID().toString();
        long currentmillis = Instant.now().toEpochMilli();
        userEventPublisher.publishEvent(this, ChannelEventType.CREATED, channelId, userId, String.valueOf(currentmillis), movie);
        return new ChannelId(channelId);
    }
}
