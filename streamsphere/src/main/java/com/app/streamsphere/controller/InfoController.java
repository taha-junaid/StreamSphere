package com.app.streamsphere.controller;

import com.app.streamsphere.models.ChannelEventType;
import com.app.streamsphere.repository.userEventsRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.time.Instant;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@RestController
@CrossOrigin(origins = "*")
public class InfoController {
    @Autowired
    private userEventsRepository userEventsRepository;
    @GetMapping("/recentWatched/{userId}")
    public ResponseEntity<Map<String, Object>> joinChannel(@PathVariable String userId) {
        long currentmillis = Instant.now().toEpochMilli();
        //Authorize user and validate channelId
        List<String> recentWatchedMoviesForUser = userEventsRepository.getRecentMoviesForUser(userId);

        Map<String, Object> response = new HashMap<>();
        response.put("message", "Authorization Success");
        response.put("recentMovies", recentWatchedMoviesForUser);

        return ResponseEntity.ok(response);
    }
}

