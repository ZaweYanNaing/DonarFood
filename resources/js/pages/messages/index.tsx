import { Head, Link } from '@inertiajs/react';
import { MessageSquare, Send } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { AppLayout } from '@/components/app-shell';

interface Conversation {
    user: {
        id: number;
        name: string;
        profile_photo_url: string | null;
    };
    last_message: {
        id: number;
        message: string;
        created_at: string;
        is_own: boolean;
        sender_id: number;
    } | null;
    unread_count: number;
    updated_at: string | null;
}

interface Props {
    conversations: Conversation[];
    unread_count: number;
}

export default function MessagesIndex({ conversations, unread_count }: Props) {
    return (
        <AppLayout>
            <Head title="Messages" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Messages</h1>
                    {unread_count > 0 && (
                        <Badge variant="secondary">{unread_count} unread</Badge>
                    )}
                </div>

                {conversations.length === 0 ? (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <MessageSquare className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                            <p className="text-muted-foreground mb-4">
                                No conversations yet. Start chatting with your friends!
                            </p>
                            <Link href="/friends">
                                <Button>
                                    <Send className="mr-2 h-4 w-4" />
                                    Go to Friends
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="space-y-2">
                        {conversations.map((conversation) => (
                            <Link key={conversation.user.id} href={`/messages/${conversation.user.id}`}>
                                <Card className="hover:bg-muted/50 cursor-pointer transition-colors">
                                    <CardContent className="py-4">
                                        <div className="flex items-center gap-4">
                                            <div className="relative">
                                                {conversation.user.profile_photo_url ? (
                                                    <img
                                                        src={conversation.user.profile_photo_url}
                                                        alt={conversation.user.name}
                                                        className="w-12 h-12 rounded-full"
                                                    />
                                                ) : (
                                                    <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                                        <span className="text-lg font-medium">
                                                            {conversation.user.name.charAt(0)}
                                                        </span>
                                                    </div>
                                                )}
                                                {conversation.unread_count > 0 && (
                                                    <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                                        {conversation.unread_count}
                                                    </span>
                                                )}
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between">
                                                    <p className="font-medium truncate">{conversation.user.name}</p>
                                                    {conversation.last_message && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {new Date(conversation.last_message.created_at).toLocaleDateString()}
                                                        </p>
                                                    )}
                                                </div>
                                                {conversation.last_message && (
                                                    <p className={`text-sm truncate ${
                                                        conversation.unread_count > 0
                                                            ? 'font-medium text-foreground'
                                                            : 'text-muted-foreground'
                                                    }`}>
                                                        {conversation.last_message.is_own && 'You: '}
                                                        {conversation.last_message.message}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}