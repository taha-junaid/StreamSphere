package com.app.streamsphere.Kafka;

import com.app.streamsphere.controller.ChannelController;
import com.app.streamsphere.models.ChannelEventType;
import com.app.streamsphere.models.UserEvent;
import org.springframework.context.ApplicationEventPublisher;
import org.springframework.stereotype.Service;

@Service
public class UserEventPublisher {

    private final ApplicationEventPublisher eventPublisher;

    public UserEventPublisher(ApplicationEventPublisher eventPublisher) {
        this.eventPublisher = eventPublisher;
    }

    public void publishEvent(Object source, ChannelEventType eventType, String channelId, String userId, String eventTime, String movie) {
        UserEvent event = new UserEvent(source, eventType, userId, channelId, eventTime, movie);
        eventPublisher.publishEvent(event);
    }
}

