package com.app.streamsphere.models;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
public
class ChannelId {
    private String channelId;
    public ChannelId(String channelId) {
        this.channelId = channelId;
    }
}
