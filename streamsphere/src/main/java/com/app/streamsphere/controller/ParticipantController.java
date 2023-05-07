package com.app.streamsphere.controller;

import com.app.streamsphere.Kafka.UserEventPublisher;
import com.app.streamsphere.models.ChannelEventType;
import com.app.streamsphere.repository.userEventsRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.time.Instant;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@RestController
@CrossOrigin(origins = "*")
public class ParticipantController {
    @Autowired
    private UserEventPublisher userEventPublisher;
    @Autowired
    private userEventsRepository userEventsRepository;
    @PostMapping("/join")
    public ResponseEntity<Map<String, Object>> joinChannel(@RequestParam String userId, @RequestParam String channelId, @RequestParam String movieId) {
        long currentmillis = Instant.now().toEpochMilli();
        //Authorize user and validate channelId
        List<String> activeUsersInChannel = userEventsRepository.getActiveUsersInChannel(channelId);
        userEventPublisher.publishEvent(this, ChannelEventType.JOINED, channelId, userId, String.valueOf(currentmillis), movieId);

        Map<String, Object> response = new HashMap<>();
        response.put("message", "Authorization Success");
        response.put("activeUsersInChannel", activeUsersInChannel);

        return ResponseEntity.ok(response);
    }

    @PostMapping("/leave")
    public ResponseEntity<String> leaveChannel(@RequestParam String userId, @RequestParam String channelId, @RequestParam String movieId) {
        long currentmillis = Instant.now().toEpochMilli();
        userEventPublisher.publishEvent(this, ChannelEventType.LEFT, channelId, userId, String.valueOf(currentmillis), movieId);
        return ResponseEntity.ok("Left Channel");
    }
}

