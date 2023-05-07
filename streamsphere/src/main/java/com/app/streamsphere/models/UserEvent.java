package com.app.streamsphere.models;

import com.app.streamsphere.controller.ChannelController;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.databind.annotation.JsonDeserialize;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;
import org.springframework.context.ApplicationEvent;

@Getter
@Setter
@JsonDeserialize
public class UserEvent extends ApplicationEvent { //This is what goes into kafka
    ChannelEventType eventType;

    String channelId;

    String userId;

    String eventTime;

    String movieId;

    public UserEvent(Object source) {
        super(source);
    }

    public UserEvent(Object source, ChannelEventType eventType, String userId, String channelId, String eventTime) {
        super(source);
        this.eventType = eventType;
        this.userId = userId;
        this.channelId = channelId;
        this.eventTime = eventTime;
        this.movieId = "null";
    }

    public UserEvent(Object source, ChannelEventType eventType, String userId, String channelId, String eventTime, String movieId) {
        super(source);
        this.eventType = eventType;
        this.userId = userId;
        this.channelId = channelId;
        this.eventTime = eventTime;
        this.movieId = movieId;
    }


}

